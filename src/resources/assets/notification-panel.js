/*!
 * notification-bell — painel administrativo.
 * Vanilla JS autocontido: sem jQuery, sem Alpine, sem build (npm).
 * Mantém o contrato de campos/rotas do PanelNotificationController.
 */
(function () {
    "use strict";

    var root = document.querySelector("[data-nbp-root]");
    if (!root) return;

    var i18n = {};
    try {
        i18n = JSON.parse(document.getElementById("nbp-i18n").textContent || "{}");
    } catch (e) {
        i18n = {};
    }

    function t(key, replacements) {
        var str = i18n[key] || key;
        if (replacements) {
            Object.keys(replacements).forEach(function (k) {
                str = str.replace(":" + k, replacements[k]);
            });
        }
        return str;
    }

    var routes = JSON.parse(root.getAttribute("data-routes") || "{}");
    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute("content");
    var userNameKey = root.getAttribute("data-name-column") || "name";

    /* ------------------------------------------------------------------ *
     * Tema claro/escuro
     * ------------------------------------------------------------------ */
    var THEME_KEY = "nbp-theme";
    var themeToggleBtn = document.querySelector("[data-nbp-theme-toggle]");

    function applyTheme(theme) {
        if (theme === "dark") {
            root.classList.add("nbp-dark");
            root.classList.remove("nbp-theme-auto");
        } else if (theme === "light") {
            root.classList.remove("nbp-dark");
            root.classList.remove("nbp-theme-auto");
        } else {
            root.classList.remove("nbp-dark");
            root.classList.add("nbp-theme-auto");
        }
    }

    (function initTheme() {
        var saved = null;
        try {
            saved = localStorage.getItem(THEME_KEY);
        } catch (e) {}
        applyTheme(saved || "auto");
    })();

    if (themeToggleBtn) {
        themeToggleBtn.addEventListener("click", function () {
            var isDark = root.classList.contains("nbp-dark");
            var next = isDark ? "light" : "dark";
            applyTheme(next);
            try {
                localStorage.setItem(THEME_KEY, next);
            } catch (e) {}
        });
    }

    /* ------------------------------------------------------------------ *
     * Helpers de rede
     * ------------------------------------------------------------------ */
    function request(url, options) {
        options = options || {};
        var headers = Object.assign(
            {
                "X-Requested-With": "XMLHttpRequest",
                "X-CSRF-TOKEN": csrfToken,
                Accept: "application/json",
            },
            options.headers || {}
        );

        return fetch(url, {
            method: options.method || "GET",
            headers: headers,
            body: options.body,
            credentials: "same-origin",
        }).then(function (response) {
            return response
                .json()
                .catch(function () {
                    return {};
                })
                .then(function (data) {
                    if (!response.ok) {
                        var err = new Error(data.message || "Request failed");
                        err.status = response.status;
                        err.data = data;
                        throw err;
                    }
                    return data;
                });
        });
    }

    function toFormBody(obj) {
        var params = new URLSearchParams();
        Object.keys(obj).forEach(function (key) {
            var value = obj[key];
            if (value === null || value === undefined) return;

            if (Array.isArray(value)) {
                value.forEach(function (item) {
                    params.append(key + "[]", item);
                });
            } else {
                params.append(key, value);
            }
        });
        return params;
    }

    /* ------------------------------------------------------------------ *
     * Toasts
     * ------------------------------------------------------------------ */
    var toastsContainer = document.getElementById("nbp-toasts");

    var TOAST_ICONS = {
        success: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        error: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        warning: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86l-8.18 14.18A2 2 0 004 21h16a2 2 0 001.89-2.96L13.71 3.86a2 2 0 00-3.42 0z"/></svg>',
        info: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
    };

    var TOAST_TITLES = {
        success: t("toast_success_title"),
        error: t("toast_error_title"),
        warning: t("toast_error_title"),
        info: t("toast_info_title"),
    };

    function showToast(type, message, title) {
        if (!toastsContainer) return;
        type = TOAST_ICONS[type] ? type : "info";

        var toast = document.createElement("div");
        toast.className = "nbp-toast nbp-toast-" + type;
        toast.setAttribute("role", "status");
        toast.setAttribute("aria-live", "polite");
        toast.innerHTML =
            '<span class="nbp-toast-icon">' + TOAST_ICONS[type] + "</span>" +
            '<div class="nbp-toast-body">' +
            '<p class="nbp-toast-title"></p>' +
            '<p class="nbp-toast-message"></p>' +
            "</div>" +
            '<button type="button" class="nbp-toast-close" aria-label="' + t("close") + '">' +
            '<svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>' +
            "</button>";

        toast.querySelector(".nbp-toast-title").textContent = title || TOAST_TITLES[type];
        toast.querySelector(".nbp-toast-message").textContent = message || "";

        toastsContainer.appendChild(toast);
        requestAnimationFrame(function () {
            toast.classList.add("nbp-toast-visible");
        });

        var timer = setTimeout(function () {
            dismissToast(toast);
        }, 5000);

        toast.querySelector(".nbp-toast-close").addEventListener("click", function () {
            clearTimeout(timer);
            dismissToast(toast);
        });
    }

    function dismissToast(toast) {
        toast.classList.remove("nbp-toast-visible");
        setTimeout(function () {
            toast.remove();
        }, 200);
    }

    /* ------------------------------------------------------------------ *
     * Modais genéricos (data-nbp-modal)
     * ------------------------------------------------------------------ */
    var openModals = [];

    function openModal(id) {
        var overlay = document.getElementById(id);
        if (!overlay) return;
        overlay.classList.remove("nbp-hidden");
        overlay.setAttribute("aria-hidden", "false");
        openModals.push(id);

        var focusTarget = overlay.querySelector("[data-nbp-autofocus]") || overlay.querySelector(".nbp-modal");
        if (focusTarget) {
            setTimeout(function () {
                focusTarget.focus();
            }, 30);
        }

        document.body.style.overflow = "hidden";
    }

    function closeModal(id) {
        var overlay = document.getElementById(id);
        if (!overlay) return;
        overlay.classList.add("nbp-hidden");
        overlay.setAttribute("aria-hidden", "true");
        openModals = openModals.filter(function (m) {
            return m !== id;
        });
        if (openModals.length === 0) {
            document.body.style.overflow = "";
        }
    }

    document.addEventListener("click", function (event) {
        var openTrigger = event.target.closest("[data-nbp-open]");
        if (openTrigger) {
            openModal(openTrigger.getAttribute("data-nbp-open"));
            return;
        }

        var closeTrigger = event.target.closest("[data-nbp-close]");
        if (closeTrigger) {
            var overlay = closeTrigger.closest(".nbp-overlay");
            if (overlay) closeModal(overlay.id);
            return;
        }

        // Clique no overlay (fora do card) fecha o modal
        if (event.target.classList.contains("nbp-overlay")) {
            closeModal(event.target.id);
        }
    });

    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape" && openModals.length > 0) {
            closeModal(openModals[openModals.length - 1]);
        }
    });

    /* ------------------------------------------------------------------ *
     * Tabela: recarregar via AJAX (filtros, paginação)
     * ------------------------------------------------------------------ */
    var tableContainer = document.getElementById("nbp-table-container");
    var filterForm = document.getElementById("nbp-filter-form");
    var itemsCountEl = document.getElementById("nbp-items-count");

    function refreshTable(queryString) {
        var url = routes.index + (queryString ? "?" + queryString : "");
        return request(url, {
            headers: { "X-Requested-With": "XMLHttpRequest" },
        }).then(function (response) {
            if (response.success) {
                tableContainer.innerHTML = response.html;
                if (itemsCountEl) {
                    itemsCountEl.textContent = t("table_items_count", { count: response.total });
                }
                bindTableEvents();
            }
        }).catch(function () {
            showToast("error", t("error_generic"));
        });
    }

    function currentFilterQuery() {
        if (!filterForm) return "";
        return new URLSearchParams(new FormData(filterForm)).toString();
    }

    if (filterForm) {
        filterForm.addEventListener("submit", function (event) {
            event.preventDefault();
            refreshTable(currentFilterQuery());
        });

        var clearBtn = filterForm.querySelector("[data-nbp-clear-filters]");
        if (clearBtn) {
            clearBtn.addEventListener("click", function () {
                filterForm.reset();
                refreshTable("");
            });
        }
    }

    // Delegação de clique em paginação (links renderizados pelo Laravel)
    document.addEventListener("click", function (event) {
        var link = event.target.closest("#nbp-table-container .nbp-pagination-wrap a");
        if (!link) return;
        event.preventDefault();
        var url = new URL(link.href, window.location.origin);
        var query = url.search.replace(/^\?/, "");
        refreshTable(query);
    });

    /* ------------------------------------------------------------------ *
     * Seleção múltipla + barra de ações em massa
     * ------------------------------------------------------------------ */
    function bindTableEvents() {
        var selectAll = tableContainer.querySelector("[data-nbp-select-all]");
        var rowChecks = Array.prototype.slice.call(
            tableContainer.querySelectorAll("[data-nbp-select-row]")
        );
        var bulkBar = tableContainer.querySelector("[data-nbp-bulkbar]");
        var bulkCount = tableContainer.querySelector("[data-nbp-bulk-count]");

        function updateBulkBar() {
            var checked = rowChecks.filter(function (c) {
                return c.checked;
            });

            if (bulkBar) {
                bulkBar.classList.toggle("nbp-hidden", checked.length === 0);
            }
            if (bulkCount) {
                bulkCount.textContent = t("bulk_selected_count", { count: checked.length });
            }
            if (selectAll) {
                selectAll.checked = checked.length > 0 && checked.length === rowChecks.length;
                selectAll.indeterminate = checked.length > 0 && checked.length < rowChecks.length;
            }

            rowChecks.forEach(function (c) {
                var row = c.closest("tr");
                if (row) row.classList.toggle("nbp-row-selected", c.checked);
            });
        }

        if (selectAll) {
            selectAll.addEventListener("change", function () {
                rowChecks.forEach(function (c) {
                    c.checked = selectAll.checked;
                });
                updateBulkBar();
            });
        }

        rowChecks.forEach(function (c) {
            c.addEventListener("change", updateBulkBar);
        });

        var clearSelectionBtn = tableContainer.querySelector("[data-nbp-clear-selection]");
        if (clearSelectionBtn) {
            clearSelectionBtn.addEventListener("click", function () {
                rowChecks.forEach(function (c) {
                    c.checked = false;
                });
                updateBulkBar();
            });
        }

        var deleteSelectedBtn = tableContainer.querySelector("[data-nbp-delete-selected]");
        if (deleteSelectedBtn) {
            deleteSelectedBtn.addEventListener("click", function () {
                var ids = rowChecks
                    .filter(function (c) {
                        return c.checked;
                    })
                    .map(function (c) {
                        return c.value;
                    });

                if (ids.length === 0) {
                    showToast("warning", t("error_select_at_least_one"));
                    return;
                }

                openDeleteConfirm({ type: "selected", ids: ids });
            });
        }

        // Ações por linha
        tableContainer.querySelectorAll("[data-nbp-view]").forEach(function (btn) {
            btn.addEventListener("click", function () {
                viewNotification(btn.getAttribute("data-nbp-view"), btn);
            });
        });

        tableContainer.querySelectorAll("[data-nbp-edit]").forEach(function (btn) {
            btn.addEventListener("click", function () {
                editNotification(btn.getAttribute("data-nbp-edit"));
            });
        });

        tableContainer.querySelectorAll("[data-nbp-delete]").forEach(function (btn) {
            btn.addEventListener("click", function () {
                openDeleteConfirm({ type: "single", id: btn.getAttribute("data-nbp-delete") });
            });
        });

        updateBulkBar();
    }

    bindTableEvents();

    /* ------------------------------------------------------------------ *
     * Excluir todas
     * ------------------------------------------------------------------ */
    var deleteAllBtn = document.querySelector("[data-nbp-delete-all]");
    if (deleteAllBtn) {
        deleteAllBtn.addEventListener("click", function () {
            openDeleteConfirm({ type: "all" });
        });
    }

    /* ------------------------------------------------------------------ *
     * Modal de confirmação de exclusão (single / selected / all)
     * ------------------------------------------------------------------ */
    var deleteModalId = "nbp-modal-delete";
    var deleteModalText = document.getElementById("nbp-delete-text");
    var deleteConfirmBtn = document.getElementById("nbp-delete-confirm-btn");
    var pendingDelete = null;

    function openDeleteConfirm(payload) {
        pendingDelete = payload;

        if (deleteModalText) {
            if (payload.type === "all") {
                deleteModalText.textContent = t("delete_confirm_all_text");
            } else if (payload.type === "selected") {
                deleteModalText.textContent = t("delete_confirm_bulk_text", { count: payload.ids.length });
            } else {
                deleteModalText.textContent = t("delete_confirm_text");
            }
        }

        openModal(deleteModalId);
    }

    if (deleteConfirmBtn) {
        deleteConfirmBtn.addEventListener("click", function () {
            if (!pendingDelete) return;

            setButtonLoading(deleteConfirmBtn, t("deleting"));

            var promise;
            if (pendingDelete.type === "all") {
                promise = request(routes.destroyAll, { method: "DELETE" });
            } else if (pendingDelete.type === "selected") {
                promise = request(routes.destroySelected, {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: toFormBody({ ids: pendingDelete.ids }),
                });
            } else {
                promise = request(routes.destroy.replace("__ID__", pendingDelete.id), {
                    method: "DELETE",
                });
            }

            promise
                .then(function (response) {
                    closeModal(deleteModalId);
                    refreshTable(currentFilterQuery());
                    showToast("success", response.message);
                })
                .catch(function (err) {
                    showToast("error", (err.data && err.data.message) || t("error_generic"));
                })
                .finally(function () {
                    restoreButton(deleteConfirmBtn);
                    pendingDelete = null;
                });
        });
    }

    /* ------------------------------------------------------------------ *
     * Modal de criação
     * ------------------------------------------------------------------ */
    var createForm = document.getElementById("nbp-create-form");
    if (createForm) {
        createForm.addEventListener("submit", function (event) {
            event.preventDefault();
            clearFormErrors(createForm);

            var submitBtn = createForm.querySelector('[type="submit"]');
            setButtonLoading(submitBtn, t("saving"));

            var body = new URLSearchParams(new FormData(createForm));

            request(routes.store, {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: body,
            })
                .then(function (response) {
                    closeModal("nbp-modal-create");
                    createForm.reset();
                    refreshTable(currentFilterQuery());
                    showToast("success", response.message);
                })
                .catch(function (err) {
                    if (err.status === 422 && err.data && err.data.errors) {
                        displayFormErrors(createForm, err.data.errors);
                        showToast("error", t("validation_error"));
                    } else {
                        showToast("error", (err.data && err.data.message) || t("error_generic"));
                    }
                })
                .finally(function () {
                    restoreButton(submitBtn);
                });
        });
    }

    /* ------------------------------------------------------------------ *
     * Modal de edição
     * ------------------------------------------------------------------ */
    var editForm = document.getElementById("nbp-edit-form");
    var editIdInput = document.getElementById("nbp-edit-id");

    function editNotification(id) {
        clearFormErrors(editForm);

        request(routes.show.replace("__ID__", id))
            .then(function (response) {
                if (!response.success) {
                    showToast("error", response.message || t("error_not_found"));
                    return;
                }

                var n = response.notification;
                editIdInput.value = n.id;
                editForm.querySelector("#nbp-edit-title").value = n.title || "";
                editForm.querySelector("#nbp-edit-message").value = stripHtmlIfNeeded(n.message);
                editForm.querySelector("#nbp-edit-type").value = n.type || "info";
                editForm.querySelector("#nbp-edit-recipient").value = n.user_id || "";
                editForm.querySelector("#nbp-edit-url").value = (n.data && n.data.action_url) || n.action_url || "";
                editForm.querySelector("#nbp-edit-processing").value = "immediate";

                openModal("nbp-modal-edit");
            })
            .catch(function () {
                showToast("error", t("error_loading_details"));
            });
    }

    function stripHtmlIfNeeded(message) {
        if (!message) return "";
        // Mensagens antigas podem ter sido salvas como HTML (Quill) ou JSON delta.
        try {
            JSON.parse(message);
            return message; // Deixa como está: formato não textual simples.
        } catch (e) {
            // segue fluxo
        }
        if (/<[a-z][\s\S]*>/i.test(message)) {
            var div = document.createElement("div");
            div.innerHTML = message;
            return div.textContent || div.innerText || "";
        }
        return message;
    }

    if (editForm) {
        editForm.addEventListener("submit", function (event) {
            event.preventDefault();
            clearFormErrors(editForm);

            var id = editIdInput.value;
            var submitBtn = editForm.querySelector('[type="submit"]');
            setButtonLoading(submitBtn, t("saving"));

            var body = new URLSearchParams(new FormData(editForm));

            request(routes.update.replace("__ID__", id), {
                method: "PUT",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: body,
            })
                .then(function (response) {
                    closeModal("nbp-modal-edit");
                    refreshTable(currentFilterQuery());
                    showToast("success", response.message);
                })
                .catch(function (err) {
                    if (err.status === 422 && err.data && err.data.errors) {
                        displayFormErrors(editForm, err.data.errors);
                        showToast("error", t("validation_error"));
                    } else {
                        showToast("error", (err.data && err.data.message) || t("error_generic"));
                    }
                })
                .finally(function () {
                    restoreButton(submitBtn);
                });
        });
    }

    /* ------------------------------------------------------------------ *
     * Modal de visualização
     * ------------------------------------------------------------------ */
    var TYPE_LABELS = {
        info: t("type_info"),
        success: t("type_success"),
        warning: t("type_warning"),
        error: t("type_error"),
    };

    function viewNotification(id, triggerBtn) {
        var originalHtml = triggerBtn ? triggerBtn.innerHTML : null;
        if (triggerBtn) {
            triggerBtn.disabled = true;
            triggerBtn.innerHTML = spinnerSvg();
        }

        request(routes.show.replace("__ID__", id))
            .then(function (response) {
                if (!response.success) {
                    showToast("error", response.message || t("error_not_found"));
                    return;
                }

                var n = response.notification;
                document.getElementById("nbp-view-id").value = n.id;
                document.getElementById("nbp-view-title").textContent = n.title;
                document.getElementById("nbp-view-message").textContent = stripHtmlIfNeeded(n.message);

                var badge = document.getElementById("nbp-view-type-badge");
                badge.textContent = TYPE_LABELS[n.type] || n.type;
                badge.className = "nbp-badge " + typeBadgeClass(n.type);

                var date = n.created_at ? new Date(n.created_at) : null;
                document.getElementById("nbp-view-date").textContent = date
                    ? date.toLocaleDateString() + " " + date.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" })
                    : "";

                var statusEl = document.getElementById("nbp-view-status");
                statusEl.innerHTML =
                    '<span class="nbp-status-dot"></span>' + (n.read_at ? t("status_read") : t("status_unread"));
                statusEl.className = "nbp-status " + (n.read_at ? "nbp-status-read" : "nbp-status-unread");

                var userLabel = n.user_name || (n.user && n.user[userNameKey]) || t("recipient_all");
                document.getElementById("nbp-view-user").textContent = userLabel;

                var actionUrl = (n.data && n.data.action_url) || n.action_url || null;
                var urlContainer = document.getElementById("nbp-view-url-container");
                if (actionUrl) {
                    urlContainer.classList.remove("nbp-hidden");
                    var link = document.getElementById("nbp-view-url");
                    link.href = actionUrl;
                    link.textContent = actionUrl;
                } else {
                    urlContainer.classList.add("nbp-hidden");
                }

                openModal("nbp-modal-view");
            })
            .catch(function () {
                showToast("error", t("error_loading_details"));
            })
            .finally(function () {
                if (triggerBtn) {
                    triggerBtn.disabled = false;
                    triggerBtn.innerHTML = originalHtml;
                }
            });
    }

    function typeBadgeClass(type) {
        switch (type) {
            case "success":
                return "nbp-badge-success";
            case "warning":
                return "nbp-badge-warning";
            case "error":
                return "nbp-badge-error";
            default:
                return "nbp-badge-info";
        }
    }

    var viewEditBtn = document.getElementById("nbp-view-edit-btn");
    if (viewEditBtn) {
        viewEditBtn.addEventListener("click", function () {
            var id = document.getElementById("nbp-view-id").value;
            closeModal("nbp-modal-view");
            setTimeout(function () {
                editNotification(id);
            }, 150);
        });
    }

    /* ------------------------------------------------------------------ *
     * Utilitários de formulário
     * ------------------------------------------------------------------ */
    function displayFormErrors(form, errors) {
        Object.keys(errors).forEach(function (field) {
            var fieldEl = form.querySelector('[data-field="' + field + '"]');
            if (!fieldEl) return;
            fieldEl.classList.add("nbp-field-error");
            var errorEl = fieldEl.querySelector(".nbp-error-text");
            if (errorEl) errorEl.textContent = errors[field][0];
        });
    }

    function clearFormErrors(form) {
        if (!form) return;
        form.querySelectorAll(".nbp-field-error").forEach(function (el) {
            el.classList.remove("nbp-field-error");
        });
        form.querySelectorAll(".nbp-error-text").forEach(function (el) {
            el.textContent = "";
        });
    }

    function setButtonLoading(btn, label) {
        btn.disabled = true;
        btn.setAttribute("data-original-html", btn.innerHTML);
        btn.innerHTML = spinnerSvg() + "<span>" + label + "</span>";
    }

    function restoreButton(btn) {
        var original = btn.getAttribute("data-original-html");
        if (original) {
            btn.innerHTML = original;
            btn.removeAttribute("data-original-html");
        }
        btn.disabled = false;
    }

    function spinnerSvg() {
        return '<svg class="nbp-spin" viewBox="0 0 24 24" fill="none" width="16" height="16"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" opacity="0.25"/><path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>';
    }

    /* ------------------------------------------------------------------ *
     * Reset dos formulários ao fechar os modais de criar/editar
     * ------------------------------------------------------------------ */
    document.querySelectorAll('[data-nbp-close][data-reset-form]').forEach(function (btn) {
        btn.addEventListener("click", function () {
            var formId = btn.getAttribute("data-reset-form");
            var form = document.getElementById(formId);
            if (form) {
                clearFormErrors(form);
            }
        });
    });
})();

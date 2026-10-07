/**
 * Toast Notifications & Secure AJAX
 * Notifications élégantes et requêtes AJAX avec gestion d'erreurs
 */

class Toast {
  constructor(message, type = "info", duration = 5000) {
    this.message = message;
    this.type = type; // info, success, warning, danger
    this.duration = duration;
    this.show();
  }

  show() {
    const toastId = "toast-" + Date.now();
    const colors = {
      success: "bg-success",
      error: "bg-danger",
      danger: "bg-danger",
      warning: "bg-warning",
      info: "bg-info",
    };
    const bgClass = colors[this.type] || colors.info;

    const toastHTML = `
            <div id="${toastId}" class="toast align-items-center text-white border-0 ${bgClass}" 
                 role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">${this.escapeHtml(this.message)}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" 
                            data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;

    // Créer le conteneur si inexistant
    let container = document.getElementById("toast-container");
    if (!container) {
      container = document.createElement("div");
      container.id = "toast-container";
      container.style.cssText =
        "position: fixed; top: 20px; right: 20px; z-index: 9999;";
      document.body.appendChild(container);
    }

    container.insertAdjacentHTML("beforeend", toastHTML);

    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, { delay: this.duration });

    toast.show();

    // Supprimer du DOM après affichage
    setTimeout(() => toastElement.remove(), this.duration + 500);
  }

  escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
  }
}

/**
 * Requête AJAX sécurisée avec gestion d'erreurs
 */
class SecureRequest {
  static async post(url, data, options = {}) {
    try {
      const formData = new URLSearchParams();

      // Ajouter les données
      for (const [key, value] of Object.entries(data)) {
        formData.append(key, value);
      }

      // Ajouter le CSRF token si présent
      const csrfToken = document.querySelector(
        'input[name="csrf_token"]',
      )?.value;
      if (csrfToken) {
        formData.append("csrf_token", csrfToken);
      }

      const response = await fetch(url, {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: formData,
        ...options,
      });

      const result = await response.json();

      if (!response.ok) {
        throw new Error(result.message || `HTTP ${response.status}`);
      }

      return result;
    } catch (error) {
      new Toast(error.message, "danger");
      throw error;
    }
  }

  static async get(url, params = {}, options = {}) {
    try {
      const queryString = new URLSearchParams(params).toString();
      const fullUrl = queryString ? `${url}?${queryString}` : url;

      const response = await fetch(fullUrl, {
        method: "GET",
        ...options,
      });

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
      }

      return await response.json();
    } catch (error) {
      new Toast(error.message, "danger");
      throw error;
    }
  }
}

/**
 * Form Validator - Validation côté client avant submit
 */
class FormValidator {
  constructor(formElement) {
    this.form = formElement;
    this.errors = {};
    this.setup();
  }

  setup() {
    this.form.addEventListener("submit", (e) => {
      if (!this.validate()) {
        e.preventDefault();
        this.showErrors();
      }
    });
  }

  validate() {
    this.errors = {};
    const inputs = this.form.querySelectorAll(
      "input[required], textarea[required], select[required]",
    );

    inputs.forEach((input) => {
      const value = input.value.trim();

      if (!value) {
        this.errors[input.name] = "Ce champ est requis";
        return;
      }

      if (input.type === "email" && !this.isValidEmail(value)) {
        this.errors[input.name] = "Email invalide";
        return;
      }

      if (input.type === "date" && !this.isValidDate(value)) {
        this.errors[input.name] = "Date invalide";
        return;
      }

      if (input.type === "number" && isNaN(value)) {
        this.errors[input.name] = "Nombre requis";
        return;
      }
    });

    return Object.keys(this.errors).length === 0;
  }

  showErrors() {
    for (const [fieldName, error] of Object.entries(this.errors)) {
      const field = this.form.querySelector(`[name="${fieldName}"]`);
      if (field) {
        field.classList.add("is-invalid");
        const feedback = document.createElement("div");
        feedback.className = "invalid-feedback d-block";
        feedback.textContent = error;
        field.parentElement.appendChild(feedback);
      }
    }
  }

  isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  }

  isValidDate(dateString) {
    const regex = /^\d{4}-\d{2}-\d{2}$/;
    if (!regex.test(dateString)) return false;
    const date = new Date(dateString);
    return date instanceof Date && !isNaN(date);
  }
}

/**
 * Utilitaires
 */
const Utils = {
  /**
   * Afficher un toast de succès
   */
  success(message, duration = 5000) {
    new Toast(message, "success", duration);
  },

  /**
   * Afficher un toast d'erreur
   */
  error(message, duration = 5000) {
    new Toast(message, "error", duration);
  },

  /**
   * Afficher un toast d'avertissement
   */
  warning(message, duration = 5000) {
    new Toast(message, "warning", duration);
  },

  /**
   * Afficher un toast d'info
   */
  info(message, duration = 5000) {
    new Toast(message, "info", duration);
  },
};

// Export pour usage global
window.Toast = Toast;
window.SecureRequest = SecureRequest;
window.FormValidator = FormValidator;
window.Utils = Utils;

(function () {
  function showFlashMessages(messages) {
    if (!Array.isArray(messages) || typeof Swal === "undefined") {
      return;
    }

    messages.forEach(function (message) {
      Swal.fire({
        icon: message.type === "success" ? "success" : "error",
        title: message.title || "Aviso",
        text: message.message || "",
        confirmButtonText: "Aceptar",
      });
    });
  }

  function bindLogoutConfirm() {
    document.querySelectorAll("[data-confirm-logout]").forEach(function (form) {
      form.addEventListener("submit", function (event) {
        event.preventDefault();

        if (typeof Swal === "undefined") {
          form.submit();
          return;
        }

        Swal.fire({
          title: "Cerrar sesión",
          text: "¿Deseas salir del panel administrativo?",
          icon: "warning",
          showCancelButton: true,
          confirmButtonText: "Sí, salir",
          cancelButtonText: "Cancelar",
        }).then(function (result) {
          if (result.isConfirmed) {
            form.submit();
          }
        });
      });
    });
  }

  document.addEventListener("DOMContentLoaded", function () {
    showFlashMessages(window.APP_FLASH || []);
    bindLogoutConfirm();
  });
})();

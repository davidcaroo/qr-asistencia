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

  function bindDeleteConfirm() {
    document.querySelectorAll("[data-confirm-delete]").forEach(function (form) {
      form.addEventListener("submit", function (event) {
        event.preventDefault();

        if (typeof Swal === "undefined") {
          form.submit();
          return;
        }

        Swal.fire({
          title: "Eliminar registro",
          text: "Esta acción no se puede deshacer.",
          icon: "warning",
          showCancelButton: true,
          confirmButtonText: "Sí, eliminar",
          cancelButtonText: "Cancelar",
        }).then(function (result) {
          if (result.isConfirmed) {
            form.submit();
          }
        });
      });
    });
  }

  function bindBulkDeleteConfirm() {
    document
      .querySelectorAll("form[data-confirm-bulk-delete]")
      .forEach(function (form) {
        form.addEventListener("submit", function (event) {
          event.preventDefault();

          var label = form.getAttribute("data-bulk-label") || "registros";
          var singular = form.getAttribute("data-bulk-singular") || label;
          var selectedCount = document.querySelectorAll(
            'input[type="checkbox"][name="selected_ids[]"]:checked',
          ).length;

          if (selectedCount === 0) {
            if (typeof Swal !== "undefined") {
              Swal.fire({
                icon: "info",
                title: "Sin selección",
                text: "Marca al menos un " + singular + " para continuar.",
                confirmButtonText: "Entendido",
              });
            }
            return;
          }

          if (typeof Swal === "undefined") {
            form.submit();
            return;
          }

          Swal.fire({
            title: "Eliminar " + selectedCount + " " + label,
            text: "Esta acción no se puede deshacer.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, eliminar",
            cancelButtonText: "Cancelar",
          }).then(function (result) {
            if (result.isConfirmed) {
              form.submit();
            }
          });
        });
      });
  }

  function bindBulkSelectAll(prefix) {
    var master = document.getElementById(prefix + "SelectAll");
    if (!master) {
      return;
    }

    var checkboxes = function () {
      return Array.prototype.slice.call(
        document.querySelectorAll(
          'input[type="checkbox"][data-bulk-item="' + prefix + '"]',
        ),
      );
    };

    function syncMasterState() {
      var items = checkboxes();
      var checkedItems = items.filter(function (item) {
        return item.checked;
      });

      master.checked = items.length > 0 && checkedItems.length === items.length;
      master.indeterminate =
        checkedItems.length > 0 && checkedItems.length < items.length;
    }

    master.addEventListener("change", function () {
      checkboxes().forEach(function (item) {
        item.checked = master.checked;
      });
      syncMasterState();
    });

    document.addEventListener("change", function (event) {
      if (
        event.target &&
        event.target.matches(
          'input[type="checkbox"][data-bulk-item="' + prefix + '"]',
        )
      ) {
        syncMasterState();
      }
    });

    syncMasterState();
  }

  function initEmployeesTable() {
    if (
      typeof jQuery === "undefined" ||
      typeof jQuery.fn.DataTable === "undefined"
    ) {
      return;
    }

    var table = jQuery("#employeesTable");

    if (!table.length) {
      return;
    }

    var dataTable = table.DataTable({
      dom: "lrtip",
      pageLength: 10,
      lengthMenu: [10, 25, 50, 100],
      order: [[1, "asc"]],
      language: {
        decimal: ".",
        emptyTable: "No hay empleados registrados todavía.",
        info: "Mostrando _START_ a _END_ de _TOTAL_ empleados",
        infoEmpty: "Mostrando 0 a 0 de 0 empleados",
        infoFiltered: "(filtrado de _MAX_ empleados)",
        lengthMenu: "Mostrar _MENU_ empleados",
        loadingRecords: "Cargando...",
        processing: "Procesando...",
        search: "Buscar:",
        zeroRecords: "No se encontraron resultados",
        paginate: {
          first: "Primero",
          last: "Último",
          next: "Siguiente",
          previous: "Anterior",
        },
      },
    });

    jQuery.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
      if (settings.nTable !== table.get(0)) {
        return true;
      }

      var node = dataTable.row(dataIndex).node();
      if (!node) {
        return true;
      }

      var row = jQuery(node);
      var group = row.data("group") || "";
      var status = row.data("status") || "";
      var selectedGroup = jQuery("#employeeGroupFilter").val() || "";
      var selectedStatus = jQuery("#employeeStatusFilter").val() || "";

      if (selectedGroup && group !== selectedGroup) {
        return false;
      }

      if (selectedStatus && status !== selectedStatus) {
        return false;
      }

      return true;
    });

    jQuery("#employeeSearch").on("keyup change", function () {
      dataTable.search(this.value).draw();
    });

    jQuery("#employeeGroupFilter, #employeeStatusFilter").on(
      "change",
      function () {
        dataTable.draw();
      },
    );
  }

  function initAttendanceReportTable() {
    if (
      typeof jQuery === "undefined" ||
      typeof jQuery.fn.DataTable === "undefined"
    ) {
      return;
    }

    var table = jQuery("#attendanceReportTable");

    if (!table.length) {
      return;
    }

    table.DataTable({
      dom: "lrtip",
      pageLength: 25,
      lengthMenu: [10, 25, 50, 100],
      order: [[1, "asc"]],
      language: {
        decimal: ".",
        emptyTable: "No hay datos para el reporte seleccionado.",
        info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
        infoEmpty: "Mostrando 0 a 0 de 0 registros",
        infoFiltered: "(filtrado de _MAX_ registros)",
        lengthMenu: "Mostrar _MENU_ registros",
        loadingRecords: "Cargando...",
        processing: "Procesando...",
        search: "Buscar:",
        zeroRecords: "No se encontraron resultados",
        paginate: {
          first: "Primero",
          last: "Último",
          next: "Siguiente",
          previous: "Anterior",
        },
      },
    });
  }

  function initGroupsTable() {
    if (
      typeof jQuery === "undefined" ||
      typeof jQuery.fn.DataTable === "undefined"
    ) {
      return;
    }

    var table = jQuery("#groupsTable");

    if (!table.length) {
      return;
    }

    table.DataTable({
      dom: "lrtip",
      pageLength: 10,
      lengthMenu: [10, 25, 50, 100],
      order: [[0, "asc"]],
      language: {
        decimal: ".",
        emptyTable: "No hay grupos registrados todavía.",
        info: "Mostrando _START_ a _END_ de _TOTAL_ grupos",
        infoEmpty: "Mostrando 0 a 0 de 0 grupos",
        infoFiltered: "(filtrado de _MAX_ grupos)",
        lengthMenu: "Mostrar _MENU_ grupos",
        loadingRecords: "Cargando...",
        processing: "Procesando...",
        search: "Buscar:",
        zeroRecords: "No se encontraron resultados",
        paginate: {
          first: "Primero",
          last: "Último",
          next: "Siguiente",
          previous: "Anterior",
        },
      },
    });
  }

  document.addEventListener("DOMContentLoaded", function () {
    showFlashMessages(window.APP_FLASH || []);
    bindLogoutConfirm();
    bindDeleteConfirm();
    bindBulkDeleteConfirm();
    bindBulkSelectAll("employee");
    bindBulkSelectAll("schedule");
    initEmployeesTable();
    initAttendanceReportTable();
    initGroupsTable();
  });
})();

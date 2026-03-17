document.addEventListener("DOMContentLoaded", function () {
  function populateSelectOptions(select, options, emptyLabel) {
    if (!select) {
      return;
    }

    select.innerHTML = "";
    var emptyOption = document.createElement("option");
    emptyOption.value = "";
    emptyOption.textContent = emptyLabel;
    select.appendChild(emptyOption);

    options.forEach(function (opt) {
      var option = document.createElement("option");
      option.value = String(opt.value);
      option.textContent = opt.label;
      select.appendChild(option);
    });
  }

  function setupRequisitionRow(row) {
    if (!row) {
      return;
    }

    var categorySelect = row.querySelector(".req-category-select");
    var productSelect = row.querySelector(".req-product-select");

    if (!categorySelect || !productSelect) {
      return;
    }

    function loadProductsForCategory(categoryId) {
      var mapping = window.productsByCategory || {};
      var products = mapping[String(categoryId)] || [];
      var options = products.map(function (item) {
        return { value: item.id, label: item.label };
      });

      populateSelectOptions(
        productSelect,
        options,
        products.length ? "Select product" : "No products in selected category",
      );
    }

    categorySelect.addEventListener("change", function () {
      loadProductsForCategory(categorySelect.value);
    });

    if (categorySelect.value) {
      loadProductsForCategory(categorySelect.value);
    }
  }

  function setupProductFormNameDropdown() {
    var categorySelect = document.querySelector('select[name="category_id"]');
    var nameSelect = document.querySelector("[data-product-name]");

    if (!categorySelect || !nameSelect) {
      return;
    }

    function loadNameOptions(categoryId, selectedName) {
      var mapping = window.productNamesByCategory || {};
      var names = mapping[String(categoryId)] || [];
      var options = names.map(function (item) {
        return { value: item, label: item };
      });

      populateSelectOptions(
        nameSelect,
        options,
        names.length
          ? "Select product name"
          : "No product names for selected category",
      );

      if (selectedName) {
        nameSelect.value = selectedName;
      }
    }

    categorySelect.addEventListener("change", function () {
      loadNameOptions(categorySelect.value, "");
    });

    var initialCategory =
      window.productFormCurrentCategory || categorySelect.value;
    var initialName = window.productFormCurrentName || "";
    if (initialCategory) {
      categorySelect.value = String(initialCategory);
      loadNameOptions(initialCategory, initialName);
    }
  }

  function setupRegisterDepartmentBehavior() {
    var roleSelect = document.querySelector("[data-register-role]");
    var departmentSelect = document.querySelector("[data-register-department]");

    if (!roleSelect || !departmentSelect) {
      return;
    }

    var defaultDepartment = "Departamento de Administracao e Financas";

    function applyRoleRule() {
      if (roleSelect.value === "admin" || roleSelect.value === "warehouse") {
        departmentSelect.value = defaultDepartment;
        departmentSelect.setAttribute("readonly", "readonly");
        departmentSelect.setAttribute("disabled", "disabled");
      } else {
        departmentSelect.removeAttribute("readonly");
        departmentSelect.removeAttribute("disabled");
      }
    }

    roleSelect.addEventListener("change", applyRoleRule);
    applyRoleRule();
  }

  document.querySelectorAll("[data-confirm]").forEach(function (el) {
    el.addEventListener("click", function (e) {
      if (!confirm(el.getAttribute("data-confirm"))) {
        e.preventDefault();
      }
    });
  });

  var addItemBtn = document.getElementById("add-item-row");
  if (addItemBtn) {
    addItemBtn.addEventListener("click", function () {
      var container = document.getElementById("requisition-items");
      var first = container.querySelector(".req-item-row");
      var clone = first.cloneNode(true);
      clone.querySelectorAll("input").forEach(function (input) {
        input.value = "";
      });
      clone.querySelectorAll("select").forEach(function (select) {
        select.selectedIndex = 0;
      });
      container.appendChild(clone);
      setupRequisitionRow(clone);
    });

    setupRequisitionRow(document.querySelector(".req-item-row"));
  }

  document.addEventListener("click", function (e) {
    if (e.target.classList.contains("remove-item-row")) {
      var rows = document.querySelectorAll(".req-item-row");
      if (rows.length > 1) {
        e.target.closest(".req-item-row").remove();
      }
    }
  });

  document.querySelectorAll("[data-table-search]").forEach(function (input) {
    var target = document.getElementById(
      input.getAttribute("data-table-search"),
    );
    if (!target) {
      return;
    }

    input.addEventListener("input", function () {
      var term = input.value.toLowerCase();
      target.querySelectorAll("tbody tr").forEach(function (row) {
        var text = row.innerText.toLowerCase();
        row.style.display = text.indexOf(term) !== -1 ? "" : "none";
      });
    });
  });

  setupProductFormNameDropdown();
  setupRegisterDepartmentBehavior();
});

document.addEventListener("DOMContentLoaded", function () {
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
    });
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
});

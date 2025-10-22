document.addEventListener("DOMContentLoaded", () => {
  const categorySelect = document.getElementById("category");
  const newCategoryInput = document.getElementById("newCategoryInput");

  categorySelect.addEventListener("change", () => {
    if (categorySelect.value === "__new__") {
      newCategoryInput.classList.remove("hidden");
      newCategoryInput.required = true;
      newCategoryInput.focus();
    } else {
      newCategoryInput.classList.add("hidden");
      newCategoryInput.required = false;
      newCategoryInput.value = "";
    }
  });
});

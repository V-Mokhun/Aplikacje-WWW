function changeBackground(hexNumber) {
  document.body.style.backgroundColor = hexNumber;
}

const bgChanger = document.getElementById("bg-changer");
bgChanger.addEventListener("submit", (e) => {
  e.preventDefault();

  const colorInput = document.getElementById("color-input");
  changeBackground(colorInput.value);
});

const resetBackground = document.getElementById("reset-background");
resetBackground.addEventListener("click", () => {
  const colorInput = document.getElementById("color-input");
  colorInput.value = "#ffffff";
  changeBackground("#ffffff");
});

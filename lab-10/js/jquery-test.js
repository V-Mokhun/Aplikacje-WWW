$("#jquery-test-click").on("click", function () {
  $(this).animate(
    {
      width: 500,
      opacity: 0.4,
      fontSize: "3em",
      borderWidth: 10,
    },
    1500
  );
});

$("#jquery-test-hover").on({
  mouseover: function () {
    $(this).animate(
      {
        width: 300,
      },
      800
    );
  },
  mouseout: function () {
    $(this).animate(
      {
        width: 200,
      },
      800
    );
  },
});

$("#jquery-test-click-multiple").on("click", function () {
  if (!$(this).is(":animated")) {
    $(this).animate({
      width: "+=50",
      height: "+=10",
      opacity: "-=0.1",
      duration: 3000,
    });
  }
});

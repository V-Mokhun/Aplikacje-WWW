const options = {
  weekday: "long",
  year: "numeric",
  month: "long",
  day: "numeric",
};

let intervalId;

function updateClock() {
  const now = new Date();

  const dateString = now.toLocaleDateString("en-US", options);
  const timeString = now.toLocaleTimeString("en-US", { hour12: false });

  document.getElementById("date").textContent = dateString;
  document.getElementById("time").textContent = timeString;
}

function stopClock() {
  clearInterval(intervalId);
  document.getElementById("stop-clock").disabled = true;
  document.getElementById("start-clock").disabled = false;
}

function startClock() {
  updateClock();
  intervalId = setInterval(updateClock, 1000);
  document.getElementById("start-clock").disabled = true;
  document.getElementById("stop-clock").disabled = false;
}

document.getElementById("start-clock").addEventListener("click", startClock);
document.getElementById("stop-clock").addEventListener("click", stopClock);

startClock();

/**
 * @returns {HTMLAnchorElement}
 */
function getConnIndLink() {
  return document.getElementById('conn-ind-link');
}

function setConnectionState(title, color) {
  const connIndImg = document.getElementById('conn-ind-img');
  const connIndTitle = document.getElementById('conn-ind-title');

  getConnIndLink().title = title;
  connIndTitle.innerText = title;
  connIndImg.classList.forEach(cls => {
    if (cls.startsWith('text-')) {
      connIndImg.classList.replace(cls, `text-${color}`)
    }
  });
}

(function () {
  setConnectionState('Connecting...', 'gold');

  const eventSource = new EventSource("/api/live");
  eventSource.onopen = () => {
    setConnectionState('Connected', 'green');
  };
  eventSource.onerror = (e) => {
    console.error(e);
    setConnectionState('Connection Lost', 'red');
  };
})();

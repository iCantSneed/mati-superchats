import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static targets = ['indicatorLink', 'indicatorImage', 'indicatorTitle'];

  static values = {
    liveUrl: String,
  };

  connect() {
    this._setConnectionState('Connecting...', 'gold');
    this.eventSource = new EventSource(this.liveUrlValue);
    this.eventSource.onopen = () => {
      this._setConnectionState('Connected', 'green');
    };
    this.eventSource.onerror = (e) => {
      console.error(e);
      this._setConnectionState('Connection Lost', 'red');
    };
  }

  _setConnectionState(title, color) {
    this.indicatorLinkTarget.title = title;
    this.indicatorTitleTarget.innerText = title;
    this.indicatorImageTarget.classList.forEach(cls => {
      if (cls.startsWith('text-')) {
        this.indicatorImageTarget.classList.replace(cls, `text-${color}`)
      }
    });
  }
}

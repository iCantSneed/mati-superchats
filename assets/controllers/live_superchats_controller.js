import { Controller } from '@hotwired/stimulus';
import { connectStreamSource, disconnectStreamSource } from '@hotwired/turbo';

export default class extends Controller {
  static targets = ['indicatorLink', 'indicatorImage', 'indicatorTitle'];

  static values = {
    liveUrl: String,
  };

  connect() {
    this._setConnectionState('Connecting...', 'orange');
    this.eventSource = new EventSource(this.liveUrlValue);
    this.eventSource.onopen = () => {
      this._setConnectionState('Checking stream', 'gold');
    };
    this.eventSource.onerror = (e) => {
      console.error(e);
      this._setConnectionState('Connection Lost', 'red');
    };
    this.eventSource.addEventListener('livestream_url', (event) => {
      this._setConnectionState('Connected', 'green', event.data);
    });
    this.eventSource.addEventListener('nostream', () => {
      this._setConnectionState('No stream', 'blue');
      // TODO
    });
    connectStreamSource(this.eventSource);
  }

  disconnect() {
    disconnectStreamSource(this.eventSource);
  }

  _setConnectionState(title, color, link = null) {
    this.indicatorLinkTarget.title = title;
    this.indicatorTitleTarget.innerText = title;
    this.indicatorImageTarget.classList.forEach(cls => {
      if (cls.startsWith('text-')) {
        this.indicatorImageTarget.classList.replace(cls, `text-${color}`)
      }
    });
    if (link) {
      this.indicatorLinkTarget.href = link;
    } else {
      this.indicatorLinkTarget.removeAttribute('href');
    }
  }
}

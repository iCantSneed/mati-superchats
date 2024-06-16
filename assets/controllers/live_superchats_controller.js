import { Controller } from '@hotwired/stimulus';
import { connectStreamSource, disconnectStreamSource } from '@hotwired/turbo';

export default class extends Controller {
  static targets = ['indicatorLink', 'indicatorImage', 'indicatorTitle'];

  static values = {
    liveUrl: String,
  };

  connect() {
    this._createEventSource();
  }

  disconnect() {
    this._destroyEventSource();
    if (this.connectionTimer) {
      clearTimeout(this.connectionTimer);
    }
  }

  _createEventSource() {
    this._destroyEventSource();
    this._setConnectionState('Connecting...', 'orange');
    this.eventSource = new EventSource(this.liveUrlValue);
    this.eventSource.onopen = () => {
      this.connected = true;
      this._setConnectionState('Checking stream', 'gold');
    };
    this.eventSource.onerror = (e) => {
      console.error(e);
      if (this.connected) {
        this._setConnectionState('Connection lost', 'red');
        this._destroyEventSource();
        this.connectionTimer = setTimeout(() => this._createEventSource(), 1000);
      } else {
        this._setConnectionState('Error, refresh page', 'red');
        this._destroyEventSource();
      }
    };
    this.eventSource.addEventListener('livestream_url', (event) => {
      this._setConnectionState('Connected', 'green', event.data);
    });
    this.eventSource.addEventListener('nostream', () => {
      this._setConnectionState('No stream', 'blue');
      this._destroyEventSource();
      this.connectionTimer = setTimeout(() => this._createEventSource(), 120000);
    });
    connectStreamSource(this.eventSource);
  }

  _destroyEventSource() {
    if (this.eventSource) {
      this.eventSource.close();
      disconnectStreamSource(this.eventSource);
      this.eventSource = undefined;
    }
    this.connected = false;
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

class CardNotification extends HTMLElement {
    static get observedAttributes() {
        return ['caseNumber', 'applicant', 'typeCase', 'subtypeCase', 'date']
    }

    constructor() {
        super();
        this.attachShadow({ mode: 'open' })
    }

    connectedCallback() {
        this.render();
    }

    attributeChangedCallback(attrName, oldValue, newValue) {
        if (oldValue !== newValue && this.shadowRoot) {
            this.render();
        }
    }

    render() {
        const caseNumber = this.getAttribute('caseNumber') || '-';
        const applicant = this.getAttribute('applicant') || '-';
        const typeCase = this.getAttribute('typeCase') || '-';
        const subtypeCase = this.getAttribute('subtypeCase') || '-';
        const date = this.getAttribute('date') || '-';

        this.shadowRoot.innerHTML = `
            <style>
                :host {
                    display: block;
                }

                .content-card {
                    background-color: white;
                    border-radius: 12px;
                    box-shadow: 2px 2px 4px rgba(128, 128, 128, 0.15);
                    width: 100%;
                    padding: 15px;
                    height: min-content;
                    box-sizing: border-box;
                }

                .title-content {
                    display: flex;
                    align-items: center;
                    margin-bottom: 15px;
                }

                .title-content img {
                    width: 30px;
                    height: 30px;
                    margin-right: 15px;
                    display: block;
                }

                .title-content .title {
                    color: #E99949;
                    font-weight: bold;
                    font-size: 16px;
                    margin: 0px;
                }

                .content-card .info {
                    color: #5C5C5C;
                    font-weight: 500;
                    font-size: 14px;
                    margin: 0px 0px 10px 0px;
                }

                .content-card .info:last-child {
                    margin-bottom: 0px;
                }
            </style>

            <div class="content-card">
                <div class="title-content">
                    <img src="../resourses/img/alert.png" alt="Уведомление">
                    <p class="title">Поступило новое дело</p>
                </div>
                <p class="info">Вам назначено дело №ДЕЛО-${caseNumber}</p>
                <p class="info">Заявитель: ${applicant}</p>
                <p class="info">Тип: ${typeCase} — ${subtypeCase}</p>
                <p class="info">Дата поступления: ${date}</p>
            </div>
        `;
    }
}

customElements.define('notification-card', CardNotification);
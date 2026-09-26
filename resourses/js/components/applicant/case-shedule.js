class CardShedule extends HTMLElement {
    static get observedAttributes() {
        return ['time', 'caser', 'typeCase', 'subType'];
    }

    constructor() {
        super();
        this.attachShadow({ mode: 'open' });
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
        const time = this.getAttribute('time') || 'Дата не указана';
        const caser = this.getAttribute('caser') || '-';
        const typeCase = this.getAttribute('typeCase') || '-';
        const subType = this.getAttribute('subType') || '(-)';

        this.shadowRoot.innerHTML = `
            <style>

                :host {
                    display: block;
                }

                .content-card {
                    display: flex;
                    background-color: white;
                    border-radius: 12px;
                    box-shadow: 2px 2px 4px rgba(128, 128, 128, 0.15);
                    width: 100%;
                    padding: 15px;
                    height: min-content;
                    box-sizing: border-box;
                }

                .content-card .left-content {
                    background-color: #E99949;
                    width: 5px;
                    border-radius: 3px;
                    margin-right: 15px;
                }

                .date-content {
                    display: flex;
                    align-items: center;
                    margin-bottom: 15px;
                }

                .date-content img {
                    width: 40px;
                    height: 40px;
                    margin-right: 15px;
                }

                .date-content .date {
                    color: #5C5C5C;
                    font-weight: bold;
                    font-size: 16px;
                }

                .right-content .sub-type {
                    color: #5C5C5C;
                    font-weight: 500;
                    font-size: 16px;
                    margin: 0px 0px 10px 0px;
                }

                .right-content .status-case {
                    background-color: #4CAF50;
                    width: min-content;
                    padding: 4px 12px;
                    border-radius: 12px;
                }

                .status-case p {
                    margin: 0px;
                    color: #FAF3DD;
                    font-size: 12px;
                    font-weight: bold;
                }
            </style>

            <div class="content-card">
                <div class="left-content"></div>
                <div class="right-content">
                    <div class="date-content">
                        <img src="../resourses/img/time.png" alt="Дата">
                        <p class="date">${time}</p>
                    </div>
                    <p class="sub-type">Дело: ${caser}-2026</p>
                    <p class="sub-type">Тип: ${typeCase} ${subType}</p>
                    <div class="status-case">
                        <p>Назначено</p>
                    </div>
                </div>
            </div>
        `;
    }
}

customElements.define('case-shedule', CardShedule);
class CardCaseJudge extends HTMLElement {
    static get observedAttributes() {
        return ['id', 'status', 'statusColor', 'typeCase', 'subtypeCase', 'applicant', 'date'];
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
        const id = this.getAttribute('id') || '-';
        const status = this.getAttribute('status') || 'Не назначено';
        const statusColor = this.getAttribute('statusColor') || '#E99949';
        const typeCase = this.getAttribute('typeCase') || '-';
        const subtypeCase = this.getAttribute('subtypeCase') || '-';
        const applicant = this.getAttribute('applicant') || '-';
        const date = this.getAttribute('date') || 'Не назначено';

        this.shadowRoot.innerHTML = `
            <style>
                :host {
                    display: block;
                    box-sizing: border-box;
                }

                *, *::before, *::after {
                    box-sizing: border-box;
                }

                .content-card {
                    background-color: white;
                    border-radius: 12px;
                    box-shadow: 2px 2px 4px rgba(128, 128, 128, 0.15);
                    width: 100%;
                    padding: 15px;
                    height: min-content;
                    font-family: 'Montserrat', sans-serif;
                    display: flex;
                    flex-direction: column;
                    align-items: stretch; 
                }

                .header-content {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    margin-bottom: 15px;
                }

                .header-content .uid {
                    color: #5C5C5C;
                    font-weight: bold;
                    font-size: 16px;
                    margin: 0px;
                }

                .header-content .status-case {
                    padding: 4px 12px;
                    border-radius: 12px;
                }

                .status-case p {
                    margin: 0px;
                    color: #FAF3DD;
                    font-size: 12px;
                    font-weight: bold;
                }

                .content-card .sub-type {
                    color: #5C5C5C;
                    font-weight: 500;
                    font-size: 14px;
                    margin: 0px 0px 10px 0px;
                    line-height: 1.4;
                }

                .content-card .applicant {
                    color: #3D3D3D;
                    font-weight: bold;
                    font-size: 14px;
                    margin: 0px 0px 15px 0px;
                }

                .content-card .date-title {
                    color: #8E8E93;
                    font-weight: bold;
                    font-size: 12px;
                    text-transform: uppercase;
                    margin: 0px 0px 5px 0px;
                    letter-spacing: 0.5px;
                }

                .content-card .date {
                    color: #5C5C5C;
                    font-weight: bold;
                    font-size: 14px;
                    margin: 0px 0px 15px 0px;
                }

                .btn-approve {
                    width: auto;
                    padding: 12px 30px;
                    margin-left: auto;
                    background-color: #E99949;
                    color: #FFFFFF;
                    border: none;
                    border-radius: 6px;
                    font-family: 'Montserrat', sans-serif;
                    font-size: 14px;
                    font-weight: bold;
                    cursor: pointer;
                    transition: background-color 0.25s;
                }

                .btn-approve:hover {
                    background-color: #d88a3d;
                }
            </style>

            <div class="content-card">
                <div class="header-content">
                    <p class="uid">УИД: ДЕЛО-${id}</p>
                    <div class="status-case" style="background-color: ${statusColor};">
                        <p>${status}</p>
                    </div>
                </div>

                <p class="sub-type">Тип: ${typeCase}</p>
                <p class="sub-type">Подтип: ${subtypeCase}</p>
                <p class="applicant">Заявитель: ${applicant}</p>

                <p class="date-title">Дата и время:</p>
                <p class="date">${date}</p>

                <button type="button" class="btn-approve">Назначить</button>
            </div>
        `;

        this.shadowRoot.querySelector('.btn-approve')
            .addEventListener('click', () => {
                this.dispatchEvent(new CustomEvent('case-approve', {
                    bubbles: true,
                    composed: true,
                    detail: { id, applicant }
                }));
            });
    }
}

customElements.define('card-case-judje', CardCaseJudge);
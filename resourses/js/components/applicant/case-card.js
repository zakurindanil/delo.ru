class CardCase extends HTMLElement {
    static get observedAttributes() {
        return ['number', 'status', 'statusColor', 'type', 'subType', 'comment'];
    }

    constructor() {
        super();
        this.attachShadow({ mode: 'open'});
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
        const number = this.getAttribute('number') || '-';
        const status = this.getAttribute('status') || '';
        const statusColor = this.getAttribute('statusColor') || '';
        const type = this.getAttribute('type') || '-';
        const subType = this.getAttribute('subType') || '-';
        const comment = this.getAttribute('comment') || 'Нет комментариев';

        this.shadowRoot.innerHTML = `

            <style>
                :host {
                    display: block;
                }

                .cardCase {
                    background-color: #FFFFFF;
                    border-radius: 12px;
                    padding: 20px 25px;
                    margin-bottom: 20px;
                    box-shadow: 2px 2px 4px rgba(128, 128, 128, 0.15);
                    cursor: pointer;
                    transition: transform 0.2s, box-shadow 0.2s;
                }

                .cardCase:hover {
                    transform: translateY(-2px);
                    box-shadow: 2px 6px 12px rgba(128, 128, 128, 0.25);
                }

                .statusBackground {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    gap: 15px;
                    margin-bottom: 15px;
                }

                .cardCase .number {
                    color: #3D3D3D;
                    font-size: 20px;
                    font-weight: bold;
                    margin: 0;
                    line-height: 1.3;
                }

                .cardCase .status {
                    background-color: #E99949;
                    color: #FAF3DD;
                    padding: 4px 12px;
                    border-radius: 12px;
                    font-size: 12px;
                    font-weight: bold;
                    margin: 0;
                    white-space: nowrap;
                    flex-shrink: 0;
                }

                .cardCase .statusColor {
                    display: none;
                }

                .cardCase .type,
                .cardCase .subType {
                    color: #555555;
                    font-size: 14px;
                    font-weight: 400;
                    margin: 0 0 4px 0;
                    line-height: 1.5;
                }

                .cardCase .subType {
                    margin-bottom: 15px;
                }

                .commentBackground {
                    background-color: #F9F5EC;
                    border-radius: 8px;
                    padding: 15px;
                }

                .cardCase .comment {
                    color: #3D3D3D;
                    font-size: 14px;
                    font-weight: 400;
                    line-height: 1.5;
                    margin: 0;
                    word-wrap: break-word;
                }
            </style>

            <div class="cardCase">
                <div class="statusBackground">
                    <h2 class="number">Судебное дело № ${number}</h2>
                    <p class="status">${status}</p>
                    <p class="statusColor">${statusColor}</p>
                </div>

                <p class="type">Тип: ${type}</p>
                <p class="subType">Подтип: ${subType}</p>

                <div class="commentBackground">
                    <p class="comment">Комментарий: ${comment}</p>
                </div>
            </div>
        `;

        this.shadowRoot.querySelector('.cardCase')
            .addEventListener('click', () => {
                this.dispatchEvent(new CustomEvent('case-edit', {
                    bubbles: true,
                    composed: true,
                    detail: { number }
                }));
            });
    }   
}

customElements.define('case-card', CardCase);

class CardAdminApplicant extends HTMLElement {
    static get observedAttributes() {
        return ['id', 'status', 'statusColor', 'type', 'subType', 'comment', 'applicant', 'email', 'phone']
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
        const id = this.getAttribute('id') || '-';
        const status = this.getAttribute('status') || '';
        const statusColor = this.getAttribute('statusColor') || '';
        const type = this.getAttribute('type') || '-';
        const subType = this.getAttribute('subType') || '-';
        const comment = this.getAttribute('comment') || 'Нет комментариев';
        const applicant = this.getAttribute('applicant') || '-';
        const email = this.getAttribute('email') || '-';
        const phone = this.getAttribute('phone') || '-';

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

                .content-applicant {
                    display: flex;
                    flex-direction: row;
                    align-items: center;
                    margin-top: 15px;
                }

                .content-applicant img {
                    width: 75px;
                    height: 75px;
                    margin-right: 15px;
                }

                .applicant-info {
                    display: flex;
                    flex-direction: column;
                    align-content: center;
                }

                .applicant-info .applicant-name {
                    margin: 0px 0px 10px 0px;
                    font-size: 14px;
                    font-weight: bold;
                    color: #3D3D3D;
                }

                .applicant-info .applicant-email {
                    margin: 0px 0px 10px 0px;
                    font-size: 14px;
                    font-weight: 500;
                    color: #555555;
                }

                .applicant-info .applicant-phone {
                    margin: 0px;
                    font-size: 14px;
                    font-weight: 500;
                    color: #555555;
                }
            </style>

            <div class="cardCase">
                <div class="statusBackground">
                    <h2 class="number">Судебное дело № ${id}</h2>
                    <p class="status" style="background-color: ${statusColor};">${status}</p>
                </div>

                <p class="type">Тип: ${type}</p>
                <p class="subType">Подтип: ${subType}</p>

                <div class="commentBackground">
                    <p class="comment">Комментарий: ${comment}</p>
                </div>

                <div class="content-applicant">
                    <img src="../resourses/img/icon_user.png" alt="Аватар">
                    <div class="applicant-info">
                        <p class="applicant-name">Заявитель: ${applicant}</p>
                        <p class="applicant-email">Email: ${email}</p>
                        <p class="applicant-phone">Телефон: ${phone}</p>
                    </div>
                </div>
            </div>
        `;

        this.shadowRoot.querySelector('.cardCase').addEventListener('click', () => {
            this.dispatchEvent(new CustomEvent('case-open', {
                bubbles: true,
                composed: true,
                detail: { id }
            }));
        });
    }
}

customElements.define('card-case-applicant', CardAdminApplicant);
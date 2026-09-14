class CardAdminJudje extends HTMLElement {
    static get observedAttributes() {
        return ['judjename', 'judjeemail', 'judjephone']
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
        const judjename = this.getAttribute('judjename') || '-';
        const judjeemail = this.getAttribute('judjeemail') || '';
        const judjephone = this.getAttribute('judjephone') || '';

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
                }

                .content-judje {
                    display: flex;
                    flex-direction: row;
                    align-items: center;
                    margin-top: 15px;
                }

                .content-judje img {
                    width: 75px;
                    height: 75px;
                    margin-right: 15px;
                }

                .judje-info {
                    display: flex;
                    flex-direction: column;
                    align-content: center;
                }

                .judje-info .judje-name {
                    margin: 0px 0px 10px 0px;
                    font-size: 14px;
                    font-weight: bold;
                    color: #3D3D3D;
                }

                .judje-info .judje-email {
                    margin: 0px 0px 10px 0px;
                    font-size: 14px;
                    font-weight: 500;
                    color: #555555;
                }

                .judje-info .judje-phone {
                    margin: 0px;
                    font-size: 14px;
                    font-weight: 500;
                    color: #555555;
                }

                .btns {
                    display: flex;
                    flex-direction: column;
                }

                .judje-edit, .judje-delete {
                    margin-top:  10px;
                    width: 100%;
                    padding: 10px;
                    background-color: #E99949;
                    color: white;
                    border: none;
                    border-radius: 5px;
                    cursor: pointer;
                    font-size: 14px;
                    font-family: 'Montserrat', sans-serif;
                    font-weight: 500;
                    transition: background-color 0.3s;
                }

                .judje-edit:hover {
                    background-color: #d88a3d;
                }

                .judje-delete {
                    background-color: #ee1616;
                }

                .judje-delete:hover {
                    background-color: #b81212;
                }
            
            </style>

            <div class="cardCase">
                <div class="content-judje">
                    <img src="../resourses/img/icon_user.png" alt="Аватар">
                    <div class="judje-info">
                        <p class="judje-name">${judjename}</p>
                        <p class="judje-email">Email: ${judjeemail}</p>
                        <p class="judje-phone">Телефон: ${judjephone}</p>
                    </div>
                </div>
                    <div class="btns">
                        <button class="judje-edit">Редактировать</button>
                        <button class="judje-delete">Удалить</button>
                    </div>
                </div>
        `;

        this.shadowRoot.querySelector('.judje-edit').addEventListener('click', (e) => {e.stopPropagation();
            this.dispatchEvent(new CustomEvent('judje-edit', {
                bubbles: true,
                composed: true,
                detail: { judjename, judjeemail, judjephone }
            }));
        });

        this.shadowRoot.querySelector('.judje-delete').addEventListener('click', (e) => {e.stopPropagation();
            this.dispatchEvent(new CustomEvent('judje-delete', {
                bubbles: true,
                composed: true,
                detail: { judjename }
            }));
        });
    }
}

customElements.define('card-judje', CardAdminJudje);
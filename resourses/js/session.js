// Получает данные сессии пользователя
async function loadUser() {
    const res = await fetch('../api/user_data.php')
    const json = await res.json();

    if (!json.ok) {
        window.location.href = 'login.html';
        return;
    }

    const user = json.user;

    const role = {
        applicant: 'Заявитель',
        judge:     'Судья',
        admin:     'Администратор'
    }[user.role] || '';

    document.querySelectorAll('.username').forEach(x => x.textContent = user.fio);
    document.querySelectorAll('.role').forEach(x => x.textContent = role);

    document.querySelectorAll('.userAvatar').forEach(img => {
        if (user.avatar) img.src = user.avatar;
    }); 

    const leftName = document.querySelector('.left-content-panel .username')
    const leftRole = document.querySelector('.left-content-panel .role');
    if (leftName) leftName.textContent = user.fio;
    if (leftRole) leftRole.textContent = role;

    const fullName = document.getElementById('fullName');
    const email = document.getElementById('email');
    const phone = document.getElementById('phone');
    if (fullName) fullName.value = user.fio;
    if (email) email.value = user.email;
    if (phone) phone.value = user.phone;
}

loadUser();
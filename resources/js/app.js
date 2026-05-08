import './bootstrap';

if (window.businessUserId) {

    Echo.private(`App.Models.BusinessUser.${window.businessUserId}`)
        .notification((notification) => {

            console.log(notification);

            showToast(notification.title, notification.message);

            incrementNotificationCount();

        });

}

function showToast(title, message)
{
    alert(title + '\n' + message);
}

function incrementNotificationCount()
{
    const count = document.getElementById('notificationCount');

    if (!count) return;

    count.innerText = parseInt(count.innerText || 0) + 1;
}
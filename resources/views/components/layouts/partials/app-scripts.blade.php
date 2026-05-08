<script>
function toggleDesktopSidebar(){
    const sidebar = document.getElementById('desktopSidebar');

    if (!sidebar) return;

    sidebar.classList.toggle('hidden');
}

function openMenu(){
    document.getElementById('mobileDrawer')?.classList.add('open');
    document.getElementById('mobileOverlay')?.classList.add('open');
}

function closeMenu(){
    document.getElementById('mobileDrawer')?.classList.remove('open');
    document.getElementById('mobileOverlay')?.classList.remove('open');
}
</script>

@auth('business')
<script>
window.businessUserId = {{ auth()->guard('business')->id() }};

function urlBase64ToUint8Array(base64String)
{
    const padding = '='.repeat((4 - base64String.length % 4) % 4);

    const base64 = (base64String + padding)
        .replace(/-/g, '+')
        .replace(/_/g, '/');

    const rawData = window.atob(base64);

    return Uint8Array.from([...rawData].map(char => char.charCodeAt(0)));
}

async function initPushNotifications()
{
    if (!('serviceWorker' in navigator)) {
        return;
    }

    if (!('PushManager' in window)) {
        return;
    }

    const permission = await Notification.requestPermission();

    if (permission !== 'granted') {
        return;
    }

    const registration = await navigator.serviceWorker.register('/service-worker.js');

    const subscription = await registration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: urlBase64ToUint8Array(
            '{{ config('webpush.vapid.public_key') }}'
        )
    });

    await fetch('/push-subscribe', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(subscription)
    });
}

function bindNotificationDropdown(buttonId, dropdownId, countId)
{
    const button = document.getElementById(buttonId);
    const dropdown = document.getElementById(dropdownId);
    const count = document.getElementById(countId);

    if (!button || !dropdown) return;

    button.addEventListener('click', async function(e) {
        e.stopPropagation();

        dropdown.classList.toggle('open');

        try {
            await fetch('/owner/notifications/read', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });

            if (count) {
                count.innerText = '0';
                count.style.display = 'none';
            }
        } catch (error) {
            console.error(error);
        }
    });

    dropdown.addEventListener('click', function(e) {
        e.stopPropagation();
    });
}

document.addEventListener('click', function() {
    document.getElementById('notificationsDropdown')?.classList.remove('open');
    document.getElementById('mobileNotificationsDropdown')?.classList.remove('open');
});

initPushNotifications();

bindNotificationDropdown(
    'notificationBtn',
    'notificationsDropdown',
    'notificationCount'
);

bindNotificationDropdown(
    'mobileNotificationBtn',
    'mobileNotificationsDropdown',
    'mobileNotificationCount'
);
</script>
@endauth
@if($business->mode === 'booking')
<script>
const service = document.getElementById('service_id');
const date = document.getElementById('booking_date');
const slots = document.getElementById('slots');
const startInput = document.getElementById('start_time');

if(service && date){
    function loadSlots(){
        if(!service.value || !date.value){
            slots.innerHTML = '';
            startInput.value = '';
            return;
        }

        fetch('/b/{{ $business->slug }}/available-slots', {
            method:'POST',
            headers:{
                'Content-Type':'application/json',
                'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content
            },
            body:JSON.stringify({
                service_id:service.value,
                date:date.value
            })
        })
        .then(r => r.json())
        .then(data => {
            slots.innerHTML = '';
            startInput.value = '';

            if(!data.length){
                slots.innerHTML =
                    '<div class="muted">{{ __("messages.no_available_times") }}</div>';
                return;
            }

            data.forEach(time => {
                let div = document.createElement('div');
                div.className = 'slot';
                div.innerText = time;

                div.onclick = () => {
                    document.querySelectorAll('.slot').forEach(x => x.classList.remove('active'));
                    div.classList.add('active');
                    startInput.value = time;
                };

                slots.appendChild(div);
            });
        });
    }

    service.addEventListener('change', loadSlots);
    date.addEventListener('change', loadSlots);
}
</script>
@endif
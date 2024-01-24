const apiCache = new ApiCache();
const headers = { 'X-Requested-With': 'XMLHttpRequest' }

async function fillEvent(id) {
    try {
        var cachedData = await apiCache.getCachedOrFetch('/api/event/' + id);
        
        if (cachedData) {
            fillForm(cachedData, 'form-prenotazione');
        } else {
            console.error('Failed to retrieve cached data or fetch from API');
        }
    } catch (error) {
        console.error('An error occurred during data retrieval:', error);
    }
}

function submitEventForm(event){
    event.preventDefault();
    var form = document.getElementById('form-prenotazione');
    
    var formData = new FormData(form);
    var newEvent = formData.get('id') == null ? true : false;

    if (newEvent) {
        axios.post('/api/events', formData, { headers })
        .then(function (response) {
            console.log(response);
        })
        .catch(function (error) {
            console.log(error);
        });
    } else {
        var id = formData.get('id');

        axios.post('/api/event/' + id, formData, { headers })
        .then(function (response) {
            console.log(response);
        })
        .catch(function (error) {
            console.log(error);
        });
    }

    clearForm(form);
}
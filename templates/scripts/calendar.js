const apiCache = new ApiCache();
const headers = { 'X-Requested-With': 'XMLHttpRequest' }

async function fillEvent(id) {
    try {
        var cachedData = await apiCache.getCachedOrFetch('/api/event/' + id);
        
        if (cachedData) {
            fillForm(cachedData, ['form-prenotazione', 'form-prenotazione-summary']);

            var button = document.getElementById('form-prenotazione-summary').querySelector('#open-btn');
            button.removeAttribute('readonly');
            button.removeAttribute('disabled');

            setFileTable(id);
        } else {
            console.error('Failed to retrieve cached data or fetch from API');
        }
    } catch (error) {
        console.error('An error occurred during data retrieval:', error);
    }
}

function submitEventForm(event){
    event.preventDefault();
    var form = event.target.closest('.form');//document.getElementById('form-prenotazione');
    
    var formData = new FormData(form);
    var newEvent = formData.get('id') === null || formData.get('id') === '';

    var url = null;

    if (newEvent) {
        url = '/api/events';
    } else {
        var id = formData.get('id');
        url = '/api/event/' + id;
    }

    axios.post(url, formData, { headers })
    .then(function (response) {
        console.log(response);
    })
    .catch(function (error) {
        console.log(error);
    });

    if (newEvent) {
        clearForm(form);
    } else {
        fillEvent(id);
    }

    hideModal(form.closest(".modal").id);
}

function deleteEvent(event) {
    event.preventDefault();
    
    form = event.target.closest(".form");
    var formData = new FormData(form);

    var id = formData.get('id');

    axios.delete('/api/event/' + id, { headers })
    .then(function (response) {
        console.log(response);
    })
    .catch(function (error) {
        console.log(error);
    });

    hideModal(form.closest(".modal").id);
}
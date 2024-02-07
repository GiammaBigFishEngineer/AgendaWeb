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
        hideModal(form.closest(".modal").id);
    } else {
        fillEvent(id);
    }

    calendar.refetchEvents()
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

    calendar.refetchEvents()
    hideModal(form.closest(".modal").id);
}

function printCalendar(){
    cal_node = document.getElementById("calendar");
    
    table = cal_node.querySelector('table');

    var existingPrintStyle = table.querySelector('style[type="text/css"]:not([media])'); // Check for a style element without media attribute

    if (!existingPrintStyle) {
        // Create the style element
        var styleNode = document.createElement('style');
        styleNode.media = 'print'; // Set the media attribute to print
        styleNode.innerHTML = '* { page-break-inside: avoid; } table { border-collapse: collapse; } table th, table td { border: 1px solid #000; padding: 0.5em; }'; // Set the CSS styles for print
        
        // Append the style node as a child to the table
        table.appendChild(styleNode);
    }

    //Get the dates for the header
    const currentDate = calendar.getDate();
    const currentMonth = currentDate.toLocaleString('en', { month: 'long' });
    const currentYear = currentDate.getFullYear();

    //Expands calendar
    toggleSummaryColumn()
    calendar.render()

    //Prints and sets it back as it was before
    setTimeout(function(){
        printJS({ printable: table, type: 'html', header: '<h3>' + currentMonth + ' ' + currentYear + '</h3>', style: '@page { size: A4 landscape }'})
        toggleSummaryColumn()
        calendar.render()

    }, 500)

    // printJS({ printable: table, type: 'html', header: '<h1>' + currentMonth + ' ' + currentYear + '</h1>', style: '@page { size: A4 landscape }'})


}

// JavaScript to dynamically adjust column width when the second column is hidden
function toggleSummaryColumn() {
    const summaryColumn = document.getElementById('summaryColumn');
    const calendarColumn = document.getElementById('calendarColumn');
    if (summaryColumn.classList.contains('d-none')) {
        summaryColumn.classList.remove('d-none');
        calendarColumn.classList.remove('col-lg-12');
        calendarColumn.classList.add('col-lg-8');
    } else {
        summaryColumn.classList.add('d-none');
        calendarColumn.classList.remove('col-lg-8');
        calendarColumn.classList.add('col-lg-12');
    }
}

function setupCaparre(data) {
    console.log(data);

    const vals = JSON.parse(data);
    console.log(vals);

    const caparreDiv = document.querySelector('[name="caparre"]');
    
    //Clear the list first
    caparreDiv.innerHTML = null;

    last_id = 0;
    vals.forEach((item, index) => {
        const input = document.createElement('input');
        input.type = 'number';
        input.name = `caparra-${index}`;
        input.className = 'form-control mb-1';
        input.value = item; // Set the value of the input to the array element
        caparreDiv.appendChild(input);

        last_id = index;
    });

    //Adds an empty input
    const emptyInput = document.createElement('input');
    emptyInput.type = 'number';
    emptyInput.name = `caparra-${last_id+1}`;
    emptyInput.className = 'form-control mb-1';
    caparreDiv.appendChild(emptyInput);
}
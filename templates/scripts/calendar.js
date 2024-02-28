const apiCache = new ApiCache();
const headers = { 'X-Requested-With': 'XMLHttpRequest' }

function selectColorByValue(value) {
    const options = document.querySelectorAll('#form-prenotazione .select-box__input');
    options.forEach(option => {
        if (option.value == value) {
            option.checked = true;
        }
    });
}

async function fillEvent(id) {
    apiCache.getCachedOrFetch('/api/event/' + id)
    .then(cachedData => {
        if (cachedData) {
            clearForm(document.getElementById('form-prenotazione'));
            clearForm(document.getElementById('form-prenotazione-summary'));

            fillForm(cachedData, ['form-prenotazione', 'form-prenotazione-summary']);
            selectColorByValue(cachedData["colore"]);

            var button = document.getElementById('form-prenotazione-summary').querySelector('#open-btn');
            button.removeAttribute('readonly');
            button.removeAttribute('disabled');

            setFileTable(id);
        } else {
            console.error('Failed to retrieve cached data or fetch from API');
        }
    })
    .catch(error => {
        console.error('An error occurred during data retrieval:', error);
    });
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

        setTimeout(() => {
            if (newEvent) {
                clearForm(form);
                hideModal(form.closest(".modal").id);
            } else {
                fillEvent(id);
            }
            
            calendar.refetchEvents();
        }, 100);        
    })
    .catch(function (error) {
        console.log(error);

        var error;
        if(error.response.data.message == null) {
            error = "Error"
        } else {
            error = error.response.data.message
        }

        setFormMessage(form, "error", error.response.data.message);
    });


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
    window.jsPDF = window.jspdf.jsPDF;
    cal_node = document.getElementById("calendar");
    table = cal_node.querySelector('table');

    // var doc = new jsPDF()
    // doc.autoTable({ html: cal_node });
    // doc.save('table.pdf');

    // Proceed with the html2canvas call
    setTimeout(function(){
        html2canvas(document.querySelector(".print-container"), {
            scale: 2, // Increase the scale for higher resolution
            useCORS: true,
            allowTaint: true // Allow capturing images from cross-origin sources
        }).then(canvas => {
            if (document.createElement('canvas').getContext) {
                const imgData = canvas.toDataURL('image/png');

                const pdf = new jsPDF({ orientation: 'landscape', format: 'a4' });

                var canvasWidth = canvas.width;
                var canvasHeight = canvas.height;
                var maxWidth = 297; // A4 width
                var maxHeight = 210; // A4 height
                var margin = 30;

                // Calculate the dimensions with margins and aspect ratio
                var aspectRatio = canvasWidth / canvasHeight;
                var pdfWidth, pdfHeight;

                if (aspectRatio > (maxWidth - 2 * margin) / (maxHeight - 2 * margin)) {
                pdfWidth = maxWidth - margin;
                pdfHeight = pdfWidth / aspectRatio;
                } else {
                pdfHeight = maxHeight - margin;
                pdfWidth = pdfHeight * aspectRatio;
                }

                // Calculate the positioning
                var x = (maxWidth - pdfWidth) / 2;
                var y = (maxHeight - pdfHeight) / 2;

                pdf.addImage(imgData, 'PNG', x, y, pdfWidth, pdfHeight);
                pdf.save("calendario.pdf");
            } else {
                // Canvas is not supported
                console.log('Canvas is not enabled');
            }
        });
    }, 0);



    // var existingPrintStyle = table.querySelector('style[type="text/css"]:not([media])'); // Check for a style element without media attribute

    // if (!existingPrintStyle) {
    //     // Create the style element
    //     var styleNode = document.createElement('style');
    //     styleNode.media = 'print'; // Set the media attribute to print
    //     // styleNode.innerHTML = '* { page-break-inside: avoid; } table { border-collapse: collapse; } table th, table td { border: 1px solid #000; padding: 0.5em; }'; // Set the CSS styles for print
    //     styleNode.innerHTML = `
    //     * {
    //         page-break-inside: avoid; 
    //     }
    //     table {
    //         width: 100%; /* Set the table width to 100% */
    //         table-layout: fixed; /* Fix the table layout */
    //     }
    //     th, td {
    //         width: 100px; /* Set a fixed width for table cells */
    //         word-wrap: break-word; /* Allow long content to wrap within cells */
    //     }
    //     @media print {
    //         .print-container {
    //             position: fixed; /* Set the container's position to fixed */
    //             top: 0; /* Adjust the top position as needed */
    //             left: 0; /* Adjust the left position as needed */
    //             width: 100%; /* Set the width to 100% */
    //         }
    //         table {
    //             width: 100%; /* Set the table width to 100% for print */
    //             border-collapse: collapse;
    //         }
    //         th, td {
    //             width: 85px; /* Set a fixed width for table cells for print */
    //             border: 1px solid #000; 
    //         }
    //     }
    //     `;
        
    //     // Append the style node as a child to the table
    //     table.appendChild(styleNode);
    // }

    //Get the dates for the header
    // const currentDate = calendar.getDate();
    // const currentMonth = currentDate.toLocaleString('en', { month: 'long' });
    // const currentYear = currentDate.getFullYear();

    //Expands calendar
    // toggleSummaryColumn()
    // calendar.render()

    // //Prints and sets it back as it was before
    // setTimeout(function(){
    //     printJS({ printable: table, type: 'html', header: '<h3>' + currentMonth + ' ' + currentYear + '</h3>', style: '@page { size: A4 landscape }'})
    //     // toggleSummaryColumn()
    //     calendar.render()

    // }, 500)

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
    data = JSON.parse(data)
    var vals;

    // Check if the inputObject is an object and has the expected structure
    if (typeof data === 'object' && data !== null) {
        vals = Object.values(data) 
    } else {
        vals = data;
    }

    console.log(vals);

    const caparreDiv = document.querySelector('[name="caparre"]');
    
    //Clear the list first
    caparreDiv.innerHTML = null;

    last_id = 0;
    vals.forEach((item, index) => {
        caparra = fromHTML(generateCaparraElement(index, item));
        caparreDiv.appendChild(caparra);

        last_id = index;
    });

    //Adds an empty input
    const emptyInput = fromHTML(generateCaparraElement(last_id+1));

    caparreDiv.appendChild(emptyInput);
}

function generateCaparraElement(index, item = null){

    if( item !== null ) {
        console.log(item.type)
        console.log("Bonifico", item.type == "bonifico")
        console.log("Contanti", item.type == "contanti")
        console.log("----")

        return `<div class="d-flex mb-1">
            <div class="input-group mx-1">
                <span class="input-group-text">€</span>
                <input type="number" name="caparra-value-${index}" class="form-control" value="${item.value}">
            </div>
            <select class="form-select mx-1" name="caparra-type-${index}">
                <option` + (item.type != 'bonifico' ? "selected" : "") + ` value="bonifico">Bonifico</option>
                <option` + (item.type != 'contanti' ? "selected" : "") + ` value="contanti">Contanti</option>
            </select>
        </div>`
    } else {
        return `<div class="d-flex mb-1">
            <div class="input-group mx-1">
                <span class="input-group-text">€</span>
                <input type="number" name="caparra-value-${index}" name="caparra-id" class="form-control">
            </div>
            <select class="form-select mx-1" name="caparra-type-${index}">
                <option value="" selected disabled>Tipo Pagamento</option>
                <option value="bonifico">Bonifico</option>
                <option value="contanti">Contanti</option>
            </select>
        </div>`
    }



}
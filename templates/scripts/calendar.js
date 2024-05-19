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

function toggleViewButton(enable) {
    var button = document.getElementById('form-prenotazione-summary').querySelector('#open-btn');
    if (enable) {
        button.removeAttribute('readonly');
        button.removeAttribute('disabled');
    } else {
        button.setAttribute('readonly', 'readonly');
        button.setAttribute('disabled', 'disabled');
    }
}

async function fillEvent(id) {
    apiCache.getCachedOrFetch('/api/event/' + id)
    .then(cachedData => {
        if (cachedData) {
            clearForm(document.getElementById('form-prenotazione'));
            clearForm(document.getElementById('form-prenotazione-summary'));

            fillForm(cachedData, ['form-prenotazione', 'form-prenotazione-summary']);
            selectColorByValue(cachedData["colore"]);

            setQuillText("#form-prenotazione #note-editor-2", (cachedData["note"] != null) ? cachedData["note"] : "[]");

            toggleViewButton(true)

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
                setQuillText('#note-editor', "[]");
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

        setTimeout(() => {

        calendar.refetchEvents()
        hideModal(form.closest(".modal").id);
        clearForm(document.getElementById('form-prenotazione-summary'));
        toggleViewButton(false)

        }, 100);
    })
    .catch(function (error) {
        console.log(error);
    });


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
                <input type="number" name="caparra-value-${index}" name="caparra-id" index="${index}" class="form-control">
            </div>
            <select class="form-select is-invalid mx-1" name="caparra-type-${index}" index="${index}">
                <option value="" selected disabled>Tipo Pagamento</option>
                <option value="bonifico">Bonifico</option>
                <option value="contanti">Contanti</option>
            </select>
        </div>`
    }
}

function onChangeCaparre(){
    const caparreDiv = document.querySelector('[name="caparre"]');
    caparreDiv.addEventListener('change', (event) => {
    refreshSaldo()

    if (event.target.tagName === 'SELECT') {
        if (event.target.value === '') {
            event.target.classList.add('is-invalid');
        } else {
            event.target.classList.remove('is-invalid');
        }
    }

    if (event.target.tagName === 'INPUT' || event.target.tagName == "SELECT") {
        if (checkCaparreFilled()){
            //Adds an empty input
            var caparreInputs = Array.from(document.querySelector('[name="caparre"]').querySelectorAll('input'));

            lastEl = Number(lastElementOfArray(caparreInputs).getAttribute("index"))

            // const lastCaparra = caparreInputs[caparreInputs.length].value;
            var emptyInput = fromHTML(generateCaparraElement(lastEl + 1));

            caparreDiv.appendChild(emptyInput);
        }}
    });
}

function checkCaparreFilled() {
    const caparreDiv = document.querySelector('[name="caparre"]');
    const isFilled = Array.from(caparreDiv.querySelectorAll('input, select')).every(input => input.value.trim() !== '');
    return isFilled;
}

function refreshSaldo() {
    const caparreInputs = Array.from(document.querySelector('[name="caparre"]').querySelectorAll('input'));
    const totalCaparre = caparreInputs.reduce((acc, input) => acc + parseFloat(input.value || 0), 0);
    const total = parseFloat(document.querySelector('[name="totale"]').value);
    const saldo = (total - totalCaparre).toFixed(2);
    document.querySelector('[name="saldo"]').value = Math.max(saldo, 0);
}

function setDateToCurrentMonth(){
    var calendarApi = calendar.view.currentStart;

    calendarApi.setDate(calendarApi.getDate() + 1);

    fillForm({
        "partenza" : calendarApi,
        "arrivo" : calendarApi,
    }, "form-new-prenotazione")
}

function setupQuill(editorSelector) {
    const editorNode = document.querySelector(editorSelector);

    if (!editorNode) {
        console.error("Editor node not found");
        return;
    }

    try {
        var quill = new Quill(editorNode, {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ header: [1, 2, false] }],
                    ['bold', 'italic', 'underline'],
                ],
            },
        });

        var parent = editorNode.parentNode;

        quill.on('text-change', () => {
            var val = quill.getContents();
            var noteInput = parent.querySelector('input[name="note"]');
            if (noteInput) {
                noteInput.value = JSON.stringify(val);
            } else {
                console.error("Note input not found");
            }
        });
    } catch (error) {
        console.error("Error setting up Quill:", error);
    }
}

function setQuillText(editorSelector, text){
    if(text === undefined){
        text = "[]";
    }

    var editor = document.querySelector(editorSelector);
    var quill = Quill.find(editor);

    let parsedText;
    try {
        parsedText = JSON.parse(text);
        quill.setContents(parsedText);
    } catch (error) {
        // If parsing fails, use the text as is
        parsedText = text;
        quill.setText(parsedText);
    }

}

document.addEventListener("DOMContentLoaded", function() {
    onChangeCaparre();
    document.querySelector('[name="totale"]').addEventListener('change', refreshSaldo);

    // Note editors
    var editors = ["#viewEventModal #note-editor-2", "#newEventModal #note-editor"]

    editors.forEach(editor => {
        setupQuill(editor)
    });

    // Go to year/month date picker
    var pickedGoto = flatpickr("#datepicker", {
        locale: "it",
        plugins: [
            new monthSelectPlugin({
                shorthand: true,
                dateFormat: "F Y",
                altFormat: "F Y",
                theme: "light"
            })
        ]
    });

    var gotoBtn = document.getElementById("btn_gotoDateCal");
    gotoBtn.addEventListener("click", function() {
        calendarGoTo(pickedGoto);
    });

    //Allow Modals to be draggable
    setDraggableModals();
})

function setDraggableModals(){
    interact('.draggable-modal .modal-dialog')
    .draggable({
        allowFrom: '.modal-header',
        modifiers: [
            interact.modifiers.restrictRect({
                restriction: 'parent'
            })
        ],
        listeners: {
            move(event) {
                const target = event.target;
                const x = (parseFloat(target.getAttribute('data-x')) || 0) + event.dx;
                const y = (parseFloat(target.getAttribute('data-y')) || 0) + event.dy;

                function animate() {
                    target.style.transform = `translate(${x}px, ${y}px)`;
                    target.setAttribute('data-x', x);
                    target.setAttribute('data-y', y);
                    requestAnimationFrame(animate);
                }
                animate();
            }
        }
    });

    var modals = document.querySelectorAll('.draggable-modal');
    modals.forEach(function(modal){
        modal.addEventListener('hidden.bs.modal', function () {
            var modal_dialogue = this.querySelector('.modal-dialog');

            modal_dialogue.style.transform = '';
            modal_dialogue.setAttribute('data-x', 0);
            modal_dialogue.setAttribute('data-y', 0);
        });
    })

}

function calendarGoTo(fp){
    if(fp.selectedDates[0] !== undefined){
        date = fp.selectedDates[0]
        calendar.gotoDate(date)
    }
}
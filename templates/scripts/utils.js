/**
 * Downloads a file from the given URL with the specified filename.
 *
 * @param {string} url - The URL of the file to be downloaded
 * @param {string} filename - The name to be used for the downloaded file
 * @return {void} 
 */
function downloadFile(url, filename) {
    var link = document.createElement('a');
    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

/**
 * Returns the filename from the given URL.
 *
 * @param {string} url - the URL from which to extract the filename
 * @return {string} the filename extracted from the URL
 */
function getFilenameFromUrl(url) {
    var parts = url.split('/');
    return parts[parts.length - 1];
}

/**
 * Clears the values of all input, textarea, and select elements within a given form, except for the ones specified in the exceptions array.
 *
 * @param {HTMLElement} form - The form element to clear the inputs for.
 * @param {string[]} [exceptions] - An optional array of input names to exclude from clearing.
 */
function clearForm(form, exceptions = []){

    inputs = form.querySelectorAll('input, textarea, select, [type="fakevalue"]');
    inputs.forEach(input => {
        var attr_name = input.getAttribute("name");
      
        if(!exceptions.includes(attr_name)) {
            if(input.type != "submit" && input.nodeName !== "SELECT") {
                input.value = '';
            }
          
            if(input.nodeName === "SELECT"){
                input.selectedIndex = 0;    
            }
        }
  
        if(input.getAttribute("type") == "fakevalue") {
            var val = input.getAttribute("default-value");
            input.innerHTML = val; 
        }
    });
  
}

/**
 * Fills a form with data provided.
 *
 * @param {Object} data - the data to fill the form with
 * @param {string} form_id - the id of the form to be filled
 */
function fillForm(data, form_id) {
    var form = document.getElementById(form_id);
    var formElements = form.elements;
  
   for (let i = 0; i < formElements.length; i++) {
        const element = formElements[i];
        const name = element.getAttribute('name');
  
    if (name && data.hasOwnProperty(name)) {
        if (element.nodeName === 'SELECT') {
            const selectedValue = data[name];
            for (let j = 0; j < element.options.length; j++) {
            if (element.options[j].value == selectedValue) {
                element.selectedIndex = j;
                break;
            }
        }
            } else if (element.type === 'date') {
                date = new Date(data[name]);
                element.valueAsDate = date;
            } else {
                element.value = data[name];
            }
        }
    }
}

/**
 * Returns the highest id number and the corresponding element with the given prefix.
 *
 * @param {string} prefix - The prefix for the id to search for.
 * @return {Array} An array containing the highest id number and the corresponding element.
 */
function getHighestId(prefix) {
    const elements = document.querySelectorAll(`[id^="${prefix}"]`);
    let highestId = -Infinity;
    let highestElement = null;

    elements.forEach(element => {
    const idSuffix = parseInt(element.id.substring(`${prefix}`.length));
    if (idSuffix > highestId) {
            highestId = idSuffix;
            highestElement = element;
        }
    });

    // console.log([highestId, highestElement]);

    if(highestId == -Infinity) {
        highestId = -1;
    }

    return [highestId, highestElement];
}
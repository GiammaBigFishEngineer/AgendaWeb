function getFileTr(obj){
    var html = 
    `
    <th scope="row">${obj.file.id}</th>
    <td>${obj.file.name}</td>
    <td>${obj.file.file.length > 15 ? `${obj.file.file.substring(0, 15)}...` : obj.file.file}</td>
    <td>
        <a href="${obj.link}" class="btn btn-sm btn-success" download><i class="bi bi-download"></i></a>
        <button onclick="deleteFile(${obj.file.id})" class="btn btn-sm btn-danger" download><i class="bi bi-trash"></i></a>
    </td>
    `

    return html;
}

async function setFileTable(id) {
    var table = document.getElementById('fileTable');
    var tbody = table.querySelector('tbody');

    //Clears tableBody first
    var cachedData = await apiCache.getCachedOrFetch(`/api/event/${id}/files`);

    tbody.innerHTML = "";
    cachedData.forEach(file => {
        var node = document.createElement('tr');
        node.innerHTML = getFileTr(file);
        tbody.appendChild(node);
    });
}

function sendFile(event){
    event.preventDefault();

    //Get the EventID
    var eventForm = document.getElementById('form-prenotazione')
    var eventFormData = new FormData(eventForm);
    var id = eventFormData.get('id');

    //Get the file form
    var form = document.getElementById('fileForm')
    var formData = new FormData(form);

    //Send the file
    axios.post('/api/event/' + id + '/files', formData, { headers })
    .then(function (response) {
        console.log(response);
        clearForm(form)
        fillEvent(id)
    })
    .catch(function (error) {
        console.log(error);
    });
}

function deleteFile(file_id){
    event.preventDefault();

    //Get the EventID
    var eventForm = document.getElementById('form-prenotazione')
    var eventFormData = new FormData(eventForm);
    var id = eventFormData.get('id');

    axios.delete(`/api/event/${id}/file/${file_id}`, { headers })
    .then(function (response) {
        console.log(response);
        fillEvent(id)
    })
    .catch(function (error) {
        console.log(error);
    });
}
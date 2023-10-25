import swal from 'sweetalert';

async function showAlert( actionType, buttonValue, buttonMessage ) {
    const alertConfig = {
        icon: "warning",
        dangerMode: true,
        buttons: {
            cancel: "Cancelar",
            confirm: "¡Adelante!"
        }
    };

    if (actionType === 'delete') {
        alertConfig.text = "Ojo cuidau que se borra todo!";
    } else {
        alertConfig.text = `${buttonMessage}. A continuación vas a a ${buttonValue}`;
    }

    const willProceed = await swal(alertConfig).then(willDelete => willDelete);
    return willProceed;
}

async function makeAjaxRequest( action) {
    const formData = new FormData();
    formData.append('action', action);
    formData.append('geslib_nonce', document.querySelector("#geslib_nonce").value);

    /* for (const [key, value] of Object.entries( additionalData )) {
        formData.append( key, value );
    } */
    const response = await fetch( ajaxurl, {
        method: "POST",
        credentials: "same-origin",
        body: formData
    });
    try {
        const jsonResponse = await response.json();
        if (jsonResponse.success) {
            return jsonResponse;
        } else {
            console.error( "Request was not successful" );
            return null;
        }
    } catch ( error ) {
        console.error( "Error parsing JSON: ", error );
        console.error( "Raw response: ", await response.text() );
    }
}

async function updateProgress( action, geslibContainer ) {
    const response = await makeAjaxRequest(action);
    const JsonData = typeof response.data === 'string' ? JSON.parse(response.data) : response.data;

    if ( response.success ) {
        if ( JsonData.message ) {
            geslibContainer.innerHTML += `<div>${JsonData.message}</div>`;
        }
        if(action == "geslib_check_file") {
            JsonData.loggedFiles = JSON.parse(JsonData.loggedFiles);
            geslibContainer.innerHTML += JsonData.loggedFiles.reduce( (html,item) => {
                return html + `<tr><td>${item.filename}</td><td>${item.status}</td></tr>`;
            }, '<table><thead><tr><td>Archivo</td><td>Status</td></tr></th></thead>');
            geslibContainer.innerHTML += '</table>';
        }

        geslibContainer.scrollTop = geslibContainer.scrollHeight;
    } else {
        console.error('Error');
        geslibContainer.innerHTML = 'Error!';
    }
}

document.addEventListener("DOMContentLoaded", function() {
    const geslibContainer = document.querySelector("[data-container='geslib']");
    const terminalElement = document.querySelector(".terminal");

    terminalElement.style.display = "none";
    const actions = [
        { buttonName: 'store_products', action: 'geslib_store_products', type: 'store' },
        { buttonName: 'delete_products', action: 'geslib_delete_products', type: 'delete' },
        { buttonName: 'hello_world', action: 'geslib_hello_world', type: '' },
        { buttonName: 'check_file', action: 'geslib_check_file', type: '' },
        { buttonName: 'store_log', action: 'geslib_store_log', type: '' },
        { buttonName: 'store_lines', action: 'geslib_store_lines', type: '' },
        { buttonName: 'store_categories', action: 'geslib_store_categories', type: '' },
        { buttonName: 'store_editorials', action: 'geslib_store_editorials', type: '' },
        { buttonName: 'truncate_log', action: 'geslib_truncate_log', type: '' },
    ];

    actions.forEach( async ({ buttonName, action, type }) => {
        document.querySelector( `[name='${buttonName}']` ).addEventListener( "click" , async (event) => {
            event.preventDefault();
            const buttonElement = document.querySelector( `[name='${buttonName}']` );
            const willProceed = await showAlert( type, buttonElement.value, buttonElement.getAttribute('data-swal') );

            if (willProceed) {
                terminalElement.style.display = "block";
                updateProgress( action, geslibContainer );
            }
        });
    });

    document.getElementById("geslibLogQueueProcess").addEventListener("click", async function(event) {
        event.preventDefault();

        const additionalData = {
            'log_id': event.target.dataset.logId,
            'geslib_log_queue_nonce': document.querySelector("#geslib_log_queue_nonce").value
        };

        const data = await makeAjaxRequest('geslib_log_queue', additionalData);

        if (data.success) {
            event.target.dataset.action = 'unqueue';
            event.target.textContent = 'Unqueue';
            event.target.closest('tr').querySelectorAll('td').forEach(td => {
                if (td.textContent === 'logged') {
                    td.textContent = 'queued';
                }
            });
        } else {
            console.error('Error');
        }
    });

});

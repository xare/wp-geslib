import swal from 'sweetalert';

async function showAlert( actionType, buttonValue ) {
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
        alertConfig.text = `A continuación vas a a ${buttonValue}`;
    }

    const willProceed = await swal(alertConfig).then(willDelete => willDelete);
    return willProceed;
}

async function makeAjaxRequest( action, additionalData = {} ) {
    const formData = new FormData();
    formData.append('action', action);
    formData.append('geslib_nonce', document.querySelector("#geslib_nonce").value);

    for (const [key, value] of Object.entries( additionalData )) {
        formData.append( key, value );
    }
    try {
        const response = await fetch( ajaxurl, {
            method: "POST",
            credentials: "same-origin",
            body: formData
        });
        console.info( 'Response', response );
        const jsonResponse = await response.json();
        console.info( 'jsonResponse', jsonResponse );
        if (jsonResponse.success) {
            /* const  = JSON.parse( jsonResponse.data );
            console.info( 'data', data );
            return data; */
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
    let offset = 0;
    const batchSize = 50; // Process 50 records at a time

    while (true) {
        console.info('Offset: ', offset);
        const additionalData = {
            'offset': offset,
            'batch_size': batchSize
        };
        const response = await makeAjaxRequest(action, additionalData);
        console.info( "Returned data: ", response );
        console.info( "Is operation successful?", response.success );
        console.info( "Response Data:", response.data );
        let JsonData = JSON.parse( response.data ) ;
        console.info( "Is there more data?", JsonData );

        console.info( "JsonData", JsonData );
        if ( response.success ) {
            if ( JsonData.message ) {
                geslibContainer.innerHTML += JsonData.message;
            }
            geslibContainer.innerHTML += `<div>${JsonData.message}</div>`;
            geslibContainer.innerHTML += `<div>Batch processed. Current offset: ${offset}</div>`;
            if(action == "store_products") {
                if (JsonData.hasMore) {
                    offset += batchSize;
                } else {
                    geslibContainer.innerHTML += `<div>All products processed.</div>`;
                    break;
                }
            }

        } else {
            console.error('Error');
            geslibContainer.innerHTML = 'Error!';
            break;
        }
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
        { buttonName: 'check_file', action: 'geslib_store_log', type: '' },
        { buttonName: 'store_lines', action: 'geslib_store_lines', type: '' },
        { buttonName: 'store_categories', action: 'geslib_store_categories', type: '' },
        { buttonName: 'store_editorials', action: 'geslib_store_editorials', type: '' },
        { buttonName: 'store_authors', action: 'geslib_store_authors', type: '' },
    ];

    actions.forEach( async ({ buttonName, action, type }) => {
        document.querySelector( `[name='${buttonName}']` ).addEventListener( "click" , async (event) => {
            event.preventDefault();
            const buttonElement = document.querySelector( `[name='${buttonName}']` );
            const willProceed = await showAlert( type, buttonElement.value );

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

/* eslint-disable no-console */
export default {

    logging: false,

    //
    // Logs
    // --------------------------------------------------------------------------
    log(...args) {
        if (this.logging) {
            switch (args[0]) {
                //
                // Error
                // --------------------------------------------------------------------------
                case 'error':
                    console.error(args[1]);
                    break;
                //
                // Response
                // --------------------------------------------------------------------------
                case 'response': {
                    const status = args[1].status ? args[1].status : '';
                    if (typeof args[1].data !== 'undefined') {
                        const success = args[1].data.success
                            ? ', Success: ' + args[1].data.success
                            : '';
                        console.log('%cResponse:', 'color: #25a525', status + success, args[1]);
                    } else {
                        console.log('%cResponse: ' + status, 'color: #25a525', args[1]);
                    }
                    break;
                }
            }
            if (!['error', 'response'].includes(args[0])) {
                console.log(...args);
            }
        }
    },

    //
    // Log Request
    // --------------------------------------------------------------------------
    logPostRequest(request, postURL = '') {
        if (this.logging) {
            postURL !== '' && console.log('%cpostURL:', 'color: #25a525', postURL);
            if (request instanceof FormData) {
                console.log('%cRequest:', 'color: #25a525');
                console.log(...request);
            } else {
                console.log('%cRequest:', 'color: #25a525', request);
            }
        }
    }

}

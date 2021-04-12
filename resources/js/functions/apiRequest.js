import lg from './logging';

export default (postURL, request = '', config = {}) => {
    lg.logPostRequest(request, postURL);
    return axios({
        method: request ? 'post' : 'get',
        url: postURL,
        data: request,
        ...config
    })
        .then(response => {
            lg.log('response', response);
            if(response.status === 200) {
                return response.data;
            }
        })
        .catch(error => {
            if(error.response.data.error === 'logout') {
                window.alert('Please login again.');
                window.location.href = '/logout';
            }
            lg.log('error', error.response);
            return error.response;
        });
}

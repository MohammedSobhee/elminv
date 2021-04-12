import Papa from 'papaparse';
import { capitalizeWords, changeArrayObjectKeys } from './utils';

export default (d, filename) => {
    // Format headers
    let data = changeArrayObjectKeys(d, str => capitalizeWords(str.replace('_', ' ')));
    data = Papa.unparse(data, {
        header: true
    });
    // https://stackoverflow.com/q/52240221
    const csvData = new Blob([data], { type: 'text/csv;charset=utf-8;' });
    const csvURL = navigator.msSaveBlob
        ? navigator.msSaveBlob(csvData, filename)
        : window.URL.createObjectURL(csvData);

    let tempLink = document.createElement('a');
    tempLink.href = csvURL;
    tempLink.setAttribute('download', filename);
    tempLink.click();
};

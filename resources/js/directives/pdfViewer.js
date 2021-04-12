
import { elementExists } from '../functions/utils';
import PDFObject from 'pdfobject';

const pdfViewer = {
    bind: (el, binding, vnode) => {
        const vm = vnode.context;
        elementExists('#pdf').then(() => PDFObject.embed(binding.value, '#pdf', vm.pdfOpenParams));
    }
};

export { pdfViewer, PDFObject }

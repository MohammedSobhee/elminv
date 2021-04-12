<template>
  <div>
    <canvas
      id="avatarEditorCanvas"
      ref="avatarEditorCanvas"
      :width="canvasWidth"
      :height="canvasHeight"
      class="cursorPointer"
      @dragover.prevent
      @drop="onDrop"
      @mousedown="onDragStart"
      @touchstart="onDragStart"
      @click="clicked">
    </canvas>
    <input
      ref="input"
      type="file"
      style="display:none;"
      @change="fileSelected" />
  </div>
</template>

<style type="text/css">
.cursorPointer{
    cursor: pointer;
    background-color: #0072b6;
}

.cursorGrab{
    cursor: grab;
    cursor: -webkit-grab;
    cursor: -moz-grab;
}

.cursorGrabbing{
    cursor: grabbing;
    cursor: -webkit-grabbing;
    cursor: -moz-grabbing;
}
</style>

<script>
// MIT License
// Copyright (c) 2017 two20

// Copyright (c) 2017 islas27

// Copyright (c) 2018 fpluquet

// Updated by Kristi Russell to change default SVG

// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:

// The above copyright notice and this permission notice shall be included in all
// copies or substantial portions of the Software.

// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
// SOFTWARE.
export default {
    props: {
        image: {
            type: String,
            default: ''
        },
        border: {
            type: Number,
            default: 25
        },
        borderRadius: {
            type: Number,
            default: 0
        },
        width: {
            type: Number,
            default: 200
        },
        height: {
            type: Number,
            default: 200
        },
        color: {
            type: Array,
            default: () => [0, 0, 0, 0.5]
        },
        scale: {
            type: Number,
            default: 1
        },
        rotation: {
            type: Number,
            default: 0
        }
    },
    data () {
        return {
            cursor: 'cursorPointer',
            canvas: null,
            context: null,
            dragged: false,
            imageLoaded: false,
            changed: false,
            state: {
                drag: false,
                my: null,
                mx: null,
                xxx: 'ab',
                image: {
                    x: 0,
                    y: 0,
                    resource: null
                }
            }
        };
    },
    computed: {
        canvasWidth () {
            return this.getDimensions().canvas.width;
        },
        canvasHeight () {
            return this.getDimensions().canvas.height;
        },
        rotationRadian () {
            return this.rotation * Math.PI / 180;
        }
    },
    watch: {
        state: {
            // eslint-disable-next-line no-unused-vars
            handler (val, oldval) {
                if (this.imageLoaded) {
                    this.redraw();
                }
            },
            deep: true
        },
        scale () {
            if (this.imageLoaded) {
                this.replaceImageInBounds();
                this.redraw();
            }
        },
        rotation () {
            if (this.imageLoaded) {
                this.replaceImageInBounds();
                this.redraw();
            }
        },
        borderRadius () {
            this.redraw();
        }
    },
    mounted () {
        let self = this;
        this.canvas = this.$refs.avatarEditorCanvas;
        this.context = this.canvas.getContext('2d');
        this.paint();

        if (!this.image) {
            var placeHolder = this.svgToImage('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 61.18 62.84"><path d="M55,34a24.16,24.16,0,0,1-1.56,5.88l.76,1.21,3.91,2.58a14,14,0,0,1-1,2.24,13.4,13.4,0,0,1-1.43,2l-4.19-2.08L50,45.73a24.65,24.65,0,0,1-4.3,4.3l.06,1.45,2.09,4.2a15.78,15.78,0,0,1-2,1.42,12.55,12.55,0,0,1-2.22,1L41.08,54.2l-1.23-.78a24.83,24.83,0,0,1-5,1.43,3.48,3.48,0,0,1-.24.75.41.41,0,0,1,.2.25c.08.35-.55.65-.92.72L29.31,58l4.12-.72c.36-.08,1.11.07,1.18.42s-.56.63-.93.73l-4.3,1.39,3.87-.69c.37-.08,1.13.08,1.19.42s-.57.65-.92.72L30.41,61H34l-.18.6-.81.33-.43.56s-.28.35-2,.35-2-.35-2-.35l-.43-.56-.82-.33h0a1.24,1.24,0,0,1-.39-.75c-.05-.25.18-.47.45-.6a2.52,2.52,0,0,1-.86-1.06c-.06-.28.34-.59.68-.74a2.69,2.69,0,0,1-.86-1c-.07-.36.59-.77.93-.85L29,56.26H27a2.44,2.44,0,0,1-.73-1.43,23.78,23.78,0,0,1-4.9-1.41l-1.22.78-2.59,3.89a12.23,12.23,0,0,1-2.22-1,15.78,15.78,0,0,1-2-1.42l2.08-4.2L15.47,50a25.05,25.05,0,0,1-4.31-4.3l-1.45.06L5.52,47.87a12.64,12.64,0,0,1-1.42-2,13.24,13.24,0,0,1-1-2.24L7,41.08l.78-1.21A24.63,24.63,0,0,1,6.2,34l-1.28-.68L.25,33A12.29,12.29,0,0,1,0,30.6a13.17,13.17,0,0,1,.24-2.43l4.68-.28,1.28-.68a24.63,24.63,0,0,1,1.57-5.88L7,20.11,3.09,17.53a12.42,12.42,0,0,1,2.43-4.21L9.71,15.4l1.45.07a25.42,25.42,0,0,1,4.31-4.3L15.4,9.72,13.32,5.53a14,14,0,0,1,2-1.43,13,13,0,0,1,2.22-1L20.11,7l1.22.78A24.55,24.55,0,0,1,27.2,6.21l.67-1.29L28.16.25A14.1,14.1,0,0,1,30.59,0,14.1,14.1,0,0,1,33,.25l.29,4.67L34,6.21a24.35,24.35,0,0,1,5.86,1.57L41.08,7l2.58-3.9a13.1,13.1,0,0,1,2.23,1,14,14,0,0,1,2,1.43L45.78,9.72l-.06,1.45a25,25,0,0,1,4.3,4.3l1.45-.07,4.19-2.08a13.29,13.29,0,0,1,1.43,2,13.93,13.93,0,0,1,1,2.23l-3.91,2.58-.76,1.22A24.16,24.16,0,0,1,55,27.21l1.29.68,4.67.28a12.42,12.42,0,0,1,0,4.86l-4.67.28ZM44.48,30.6A13.89,13.89,0,1,0,21.41,41a13,13,0,0,1,1.73,1.91,20.35,20.35,0,0,1,3.37,8.4h8.17a20.25,20.25,0,0,1,3.37-8.4,13.1,13.1,0,0,1,1.69-1.88A13.88,13.88,0,0,0,44.48,30.6Z" fill="#ffffff" fill-rule="evenodd"/></svg>');

            placeHolder.onload = function () {
                var x = self.canvasWidth / 2 - this.width / 2;
                var y = self.canvasHeight / 2 - this.height / 2;
                self.context.drawImage(placeHolder, x, y, this.width, this.height);
            };
        } else {
            this.loadImage(this.image);
        }
    },
    methods: {
        drawRoundedRect (context, x, y, width, height, borderRadius) {
            if (borderRadius === 0) {
                context.rect(x, y, width, height);
            } else {
                const widthMinusRad = width - borderRadius;
                const heightMinusRad = height - borderRadius;
                context.translate(x, y);
                context.arc(borderRadius, borderRadius, borderRadius, Math.PI, Math.PI * 1.5);
                context.lineTo(widthMinusRad, 0);
                context.arc(widthMinusRad, borderRadius, borderRadius, Math.PI * 1.5, Math.PI * 2);
                context.lineTo(width, heightMinusRad);
                context.arc(widthMinusRad, heightMinusRad, borderRadius, Math.PI * 2, Math.PI * 0.5);
                context.lineTo(borderRadius, height);
                context.arc(borderRadius, heightMinusRad, borderRadius, Math.PI * 0.5, Math.PI);
                context.translate(-x, -y);
            }
        },
        svgToImage (rawSVG) {
            let svg = new Blob([rawSVG], {type: 'image/svg+xml;charset=utf-8'});
            let domURL = self.URL || self.webkitURL || self;
            let url = domURL.createObjectURL(svg);
            let img = new Image();
            img.src = url;
            return img;
        },
        setState (state1) {
            var min = Math.ceil(1);
            var max = Math.floor(10000);

            this.state = state1;
            this.state.cnt = 'HELLO' + Math.floor(Math.random() * (max - min)) + min;
        },
        paint () {
            this.context.save();
            this.context.translate(0, 0);
            this.context.fillStyle = 'rgba(' + this.color.slice(0, 4).join(',') + ')';

            let borderRadius = this.borderRadius;
            const dimensions = this.getDimensions();
            const borderSize = dimensions.border;
            const height = dimensions.canvas.height;
            const width = dimensions.canvas.width;

            // clamp border radius between zero (perfect rectangle) and half the size without borders (perfect circle or "pill")
            borderRadius = Math.max(borderRadius, 0);
            borderRadius = Math.min(borderRadius, width / 2 - borderSize, height / 2 - borderSize);

            this.context.beginPath();

            // inner rect, possibly rounded
            this.drawRoundedRect(
                this.context,
                borderSize,
                borderSize,
                width - borderSize * 2,
                height - borderSize * 2,
                borderRadius);

            this.context.rect(width, 0, -width, height); // outer rect, drawn "counterclockwise"
            this.context.fill('evenodd');
            this.context.restore();
        },
        getDimensions () {
            return {
                width: this.width,
                height: this.height,
                border: this.border,
                canvas: {
                    width: this.width + (this.border * 2),
                    height: this.height + (this.border * 2)
                }
            };
        },
        onDrop (e) {
            // eslint-disable-next-line no-param-reassign
            e = e || window.event;
            e.stopPropagation();
            e.preventDefault();

            if (e.dataTransfer && e.dataTransfer.files.length) {
                // this.props.onDropFile(e)
                const reader = new FileReader();
                const file = e.dataTransfer.files[0];
                this.changed = true;
                reader.onload = e => this.loadImage(e.target.result);
                reader.readAsDataURL(file);
            }
        },
        onDragStart (e) {
            // eslint-disable-next-line no-param-reassign
            e = e || window.event;
            e.preventDefault();
            this.state.drag = true;
            this.state.mx = null;
            this.state.my = null;
            this.cursor = 'cursorGrabbing';
            let eventSubject = document;
            let hasMoved = false;
            let handleMouseUp = event => {
                this.onDragEnd(event);
                if (!hasMoved && event.targetTouches) {
                    e.target.click();
                }
                eventSubject.removeEventListener('mouseup', handleMouseUp);
                eventSubject.removeEventListener('mousemove', handleMouseMove);
                eventSubject.removeEventListener('touchend', handleMouseUp);
                eventSubject.removeEventListener('touchmove', handleMouseMove);
            };
            let handleMouseMove = event => {
                hasMoved = true;
                this.onMouseMove(event);
            };
            eventSubject.addEventListener('mouseup', handleMouseUp);
            eventSubject.addEventListener('mousemove', handleMouseMove);
            eventSubject.addEventListener('touchend', handleMouseUp);
            eventSubject.addEventListener('touchmove', handleMouseMove);
        },
        onDragEnd () {
            if (this.state.drag) {
                this.state.drag = false;
                this.cursor = 'cursorPointer';
            }
        },
        onMouseMove (e) {
            // eslint-disable-next-line no-param-reassign
            e = e || window.event;
            if (this.state.drag === false) {
                return;
            }

            this.dragged = true;
            this.changed = true;

            let imageState = this.state.image;
            const lastX = imageState.x;
            const lastY = imageState.y;

            const mousePositionX = e.targetTouches ? e.targetTouches[0].pageX : e.clientX;
            const mousePositionY = e.targetTouches ? e.targetTouches[0].pageY : e.clientY;

            const newState = {
                mx: mousePositionX,
                my: mousePositionY,
                image: imageState
            };

            if (this.state.mx && this.state.my) {
                const xDiff = (this.state.mx - mousePositionX) / this.scale;
                const yDiff = (this.state.my - mousePositionY) / this.scale;

                imageState.y = this.getBoundedY(lastY - yDiff, this.scale);
                imageState.x = this.getBoundedX(lastX - xDiff, this.scale);
            }

            this.state.mx = newState.mx;
            this.state.my = newState.my;
            this.state.image = imageState;
            // this.setState(newState)
        },
        replaceImageInBounds () {
            let imageState = this.state.image;
            imageState.y = this.getBoundedY(imageState.y, this.scale);
            imageState.x = this.getBoundedX(imageState.x, this.scale);
        },
        loadImage (imageURL) {
            let imageObj = new Image();
            let self = this;

            // imageObj.onload = () => this.handleImageReady(imageObj);
            imageObj.onload = () => {
                let imageState = self.getInitialSize(imageObj.width, imageObj.height);
                self.state.image.x = 0;
                self.state.image.y = 0;
                self.state.image.resource = imageObj;
                self.state.image.width = imageState.width;
                self.state.image.height = imageState.height;
                self.state.drag = false;
                self.$emit('vue-avatar-editor:image-ready', self.scale);
                self.imageLoaded = true;
                this.$emit('imageLoaded', self.imageLoaded);
                self.cursor = 'cursorGrab';
            };
            imageObj.onerror = err => console.log('error loading image: ', err);

            // imageObj.onerror = this.props.onLoadFailure
            if (!this.isDataURL(imageURL)) {
                imageObj.crossOrigin = 'anonymous';
            }

            imageObj.src = imageURL;
        },
        getInitialSize (width, height) {
            let newHeight;
            let newWidth;

            const dimensions = this.getDimensions();
            const canvasRatio = dimensions.height / dimensions.width;
            const imageRatio = height / width;

            if (canvasRatio > imageRatio) {
                newHeight = (this.getDimensions().height);
                newWidth = (width * (newHeight / height));
            } else {
                newWidth = (this.getDimensions().width);
                newHeight = (height * (newWidth / width));
            }

            return {
                height: newHeight,
                width: newWidth
            };
        },
        isDataURL (str) {
            if (str === null) {
                return false;
            }
            return !!str.match(/^\s*data:([a-z]+\/[a-z]+(;[a-z\-]+=[a-z\-]+)?)?(;base64)?,[a-z0-9!$&',()*+;=\-._~:@\/?%\s]*\s*$/i); // eslint-disable-line no-useless-escape
        },
        getBoundedX (x, scale) {
            var image = this.state.image;
            var dimensions = this.getDimensions();
            let width = Math.abs(image.width * Math.cos(this.rotationRadian)) + Math.abs(image.height * Math.sin(this.rotationRadian));
            let widthDiff = Math.floor((width - dimensions.width / scale) / 2);
            widthDiff = Math.max(0, widthDiff);
            return Math.max(-widthDiff, Math.min(x, widthDiff));
        },
        getBoundedY (y, scale) {
            var image = this.state.image;
            var dimensions = this.getDimensions();
            let height = Math.abs(image.width * Math.sin(this.rotationRadian)) + Math.abs(image.height * Math.cos(this.rotationRadian));
            let heightDiff = Math.floor((height - dimensions.height / scale) / 2);
            heightDiff = Math.max(0, heightDiff);
            return Math.max(-heightDiff, Math.min(y, heightDiff));
        },
        paintImage (context, image, border) {
            if (image.resource) {
                var position = this.calculatePosition(image, border);
                context.save();
                context.globalCompositeOperation = 'destination-over';
                let dimensions = this.getDimensions();
                context.translate(dimensions.canvas.width / 2, dimensions.canvas.height / 2);
                context.rotate(this.rotationRadian);
                context.translate(-dimensions.canvas.width / 2, -dimensions.canvas.height / 2);
                context.drawImage(
                    image.resource,
                    position.x,
                    position.y,
                    position.width,
                    position.height);
                context.restore();
            }
        },
        transformDataWithRotation (x, y) {
            let radian = -this.rotationRadian;
            let rx = x * Math.cos(radian) - y * Math.sin(radian);
            let ry = x * Math.sin(radian) + y * Math.cos(radian);
            return [rx, ry];
        },
        calculatePosition (image, border) {
            // eslint-disable-next-line no-param-reassign
            image = image || this.state.image;
            var dimensions = this.getDimensions();
            let width = image.width * this.scale;
            let height = image.height * this.scale;
            var widthDiff = (width - dimensions.width) / 2;
            var heightDiff = (height - dimensions.height) / 2;
            var x = image.x * this.scale;// - widthDiff;
            var y = image.y * this.scale;// - heightDiff;
            [x, y] = this.transformDataWithRotation(x, y);
            x += border - widthDiff;
            y += border - heightDiff;
            return {
                x,
                y,
                height,
                width
            };
        },
        redraw () {
            this.context.clearRect(0, 0, this.getDimensions().canvas.width, this.getDimensions().canvas.height);
            this.paint();
            this.paintImage(this.context, this.state.image, this.border);
        },
        getImage () {
            const cropRect = this.getCroppingRect();
            const image = this.state.image;

            // get actual pixel coordinates
            cropRect.x *= image.resource.width;
            cropRect.y *= image.resource.height;
            cropRect.width *= image.resource.width;
            cropRect.height *= image.resource.height;

            // create a canvas with the correct dimensions
            const canvas = document.createElement('canvas');
            canvas.width = cropRect.width;
            canvas.height = cropRect.height;

            // draw the full-size image at the correct position,
            // the image gets truncated to the size of the canvas.
            canvas.getContext('2d').drawImage(image.resource, -cropRect.x, -cropRect.y);

            return canvas;
        },
        getImageScaled () {
            const { width, height } = this.getDimensions();

            const canvas = document.createElement('canvas');
            canvas.width = width;
            canvas.height = height;

            // don't paint a border here, as it is the resulting image
            this.paintImage(canvas.getContext('2d'), this.state.image, 0);

            return canvas;
        },
        imageChanged () {
            return this.changed;
        },
        getCroppingRect () {
            const dim = this.getDimensions();
            const frameRect = {
                x: dim.border,
                y: dim.border,
                width: dim.width,
                height: dim.height
            };
            const imageRect = this.calculatePosition(this.state.image, dim.border);

            return {
                x: (frameRect.x - imageRect.x) / imageRect.width,
                y: (frameRect.y - imageRect.y) / imageRect.height,
                width: frameRect.width / imageRect.width,
                height: frameRect.height / imageRect.height
            };
        },
        clicked () {
            if (this.dragged === true) {
                this.dragged = false;
            } else {
                this.$refs.input.click();
            }
        },
        fileSelected (e) {
            var files = e.target.files || e.dataTransfer.files;
            this.$emit('select-file', files);

            if (!files.length) {
                return;
            }

            // var image = new Image();
            var reader = new FileReader();

            this.changed = true;
            reader.onload = e => this.loadImage(e.target.result);
            reader.readAsDataURL(files[0]);
        },
        resetImage () {
            let self = this;
            this.canvas = this.$refs.avatarEditorCanvas;
            this.context = this.canvas.getContext('2d');
            self.imageLoaded = false;
            this.$emit('imageLoaded', self.imageLoaded);
            self.state = {
                drag: false,
                my: null,
                mx: null,
                xxx: 'ab',
                image: {
                    x: 0,
                    y: 0,
                    resource: null
                }
            };
            this.context.clearRect(0, 0, this.getDimensions().canvas.width, this.getDimensions().canvas.height);
            this.paint();

            var placeHolder = this.svgToImage('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 61.18 62.84"><path d="M55,34a24.16,24.16,0,0,1-1.56,5.88l.76,1.21,3.91,2.58a14,14,0,0,1-1,2.24,13.4,13.4,0,0,1-1.43,2l-4.19-2.08L50,45.73a24.65,24.65,0,0,1-4.3,4.3l.06,1.45,2.09,4.2a15.78,15.78,0,0,1-2,1.42,12.55,12.55,0,0,1-2.22,1L41.08,54.2l-1.23-.78a24.83,24.83,0,0,1-5,1.43,3.48,3.48,0,0,1-.24.75.41.41,0,0,1,.2.25c.08.35-.55.65-.92.72L29.31,58l4.12-.72c.36-.08,1.11.07,1.18.42s-.56.63-.93.73l-4.3,1.39,3.87-.69c.37-.08,1.13.08,1.19.42s-.57.65-.92.72L30.41,61H34l-.18.6-.81.33-.43.56s-.28.35-2,.35-2-.35-2-.35l-.43-.56-.82-.33h0a1.24,1.24,0,0,1-.39-.75c-.05-.25.18-.47.45-.6a2.52,2.52,0,0,1-.86-1.06c-.06-.28.34-.59.68-.74a2.69,2.69,0,0,1-.86-1c-.07-.36.59-.77.93-.85L29,56.26H27a2.44,2.44,0,0,1-.73-1.43,23.78,23.78,0,0,1-4.9-1.41l-1.22.78-2.59,3.89a12.23,12.23,0,0,1-2.22-1,15.78,15.78,0,0,1-2-1.42l2.08-4.2L15.47,50a25.05,25.05,0,0,1-4.31-4.3l-1.45.06L5.52,47.87a12.64,12.64,0,0,1-1.42-2,13.24,13.24,0,0,1-1-2.24L7,41.08l.78-1.21A24.63,24.63,0,0,1,6.2,34l-1.28-.68L.25,33A12.29,12.29,0,0,1,0,30.6a13.17,13.17,0,0,1,.24-2.43l4.68-.28,1.28-.68a24.63,24.63,0,0,1,1.57-5.88L7,20.11,3.09,17.53a12.42,12.42,0,0,1,2.43-4.21L9.71,15.4l1.45.07a25.42,25.42,0,0,1,4.31-4.3L15.4,9.72,13.32,5.53a14,14,0,0,1,2-1.43,13,13,0,0,1,2.22-1L20.11,7l1.22.78A24.55,24.55,0,0,1,27.2,6.21l.67-1.29L28.16.25A14.1,14.1,0,0,1,30.59,0,14.1,14.1,0,0,1,33,.25l.29,4.67L34,6.21a24.35,24.35,0,0,1,5.86,1.57L41.08,7l2.58-3.9a13.1,13.1,0,0,1,2.23,1,14,14,0,0,1,2,1.43L45.78,9.72l-.06,1.45a25,25,0,0,1,4.3,4.3l1.45-.07,4.19-2.08a13.29,13.29,0,0,1,1.43,2,13.93,13.93,0,0,1,1,2.23l-3.91,2.58-.76,1.22A24.16,24.16,0,0,1,55,27.21l1.29.68,4.67.28a12.42,12.42,0,0,1,0,4.86l-4.67.28ZM44.48,30.6A13.89,13.89,0,1,0,21.41,41a13,13,0,0,1,1.73,1.91,20.35,20.35,0,0,1,3.37,8.4h8.17a20.25,20.25,0,0,1,3.37-8.4,13.1,13.1,0,0,1,1.69-1.88A13.88,13.88,0,0,0,44.48,30.6Z" fill="#ffffff" fill-rule="evenodd"/></svg>');

            placeHolder.onload = function () {
                var x = self.canvasWidth / 2 - this.width / 2;
                var y = self.canvasHeight / 2 - this.height / 2;
                self.context.drawImage(placeHolder, x, y, this.width, this.height);
            };
        }
    }
};
</script>

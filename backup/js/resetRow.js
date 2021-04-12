first: 1,
count: 1,
currentSize: null,

        resetRow(size) {
            if(this.start == 1) {
                this.start = 0;
                return '<div class="row">';
            } else {
                if(this.currentSize == null) {
                    this.currentSize == size;
                } else {
                    if(this.currentSize != size || this.count == size) {
                        this.currentSize = size;
                        this.count = 1;
                        return '</div><div class="row">';
                    } else {
                        this.count++;
                    }
                }
            }
        }

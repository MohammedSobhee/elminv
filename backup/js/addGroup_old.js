        addGroup(index, gid,) {
            var obj = {};
            var objf = {}
            var count = 0;
            var $index = index - 1
            for(var p in this.worksheetFields) {
                if(this.fields.hasOwnProperty(p)) {
                    if(this.fields[p].group_id == gid) {
                        objf = Object.assign(obj, this.fields[p] );
                        // objf =  Object.assign(obj, {
                        //     "worksheet_id": this.wid,
                        //     "form_field_id": this.fields[p].form_field_id,
                        //     "heading": "",
                        //     "question": "",
                        //     "description": "",
                        //     "value": 0,
                        //     "type": this.fields[p].type,
                        //     "display_size": this.fields[p].display_size,
                        //     "answer": "",
                        //     "group_id": gid,
                        //     "name": "wfield"+fid,
                        //     "wrapperID": "wfieldwrapper"+fid,
                        //     "groupID": "wfieldgroup" + gid,
                        //     "sameGroup": 1,
                        //     "colSize": "col-md-" + this.fields[p].display_size
                        // })
                    }
                }
            }
            this.fields.splice($index, 0, obj);

            // this.fields.splice($index, 0, {
            //     "worksheet_id": this.wid,
            //     "form_field_id": fid,
            //     "heading": "",
            //     "question": "",
            //     "description": "",
            //     "value": 0,
            //     "type": 0,
            //     "display_size": size,
            //     "answer": "",
            //     "group_id": 2,
            //     "name": "wfield"+fid,
            //     "wrapperID": "wfieldwrapper"+fid,
            //     "groupID": "wfieldgroup" + gid,
            //     "sameGroup": 1,
            //     "colSize": "col-md-"+size
            // });
            // let checkEmptyLines = this.lines.filter(line => line.number === null)
            // if (checkEmptyLines.length >= 1 && this.lines.length > 0) return
            // this.fields.push({
            //     countryCode: null,
            //     number: null,
            //     phoneUsageType: null
            // })
        },

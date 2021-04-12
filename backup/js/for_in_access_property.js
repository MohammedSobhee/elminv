                    for(const pg in this.wsgroups) { // has to be a better way...
                        if(this.wsgroups[pg].group_id == groupID) {
                            for(const p in this.wsgroups[pg].fields) {
                                if(this.wsgroups[pg].fields[p].form_field_id == fieldID)
                                    this.wsgroups[pg].fields[p].answer = e.target.result
                            }
                        }
                    }

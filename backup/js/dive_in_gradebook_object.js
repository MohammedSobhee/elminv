        //
        // Deep Dive Modification
        // --------------------------------------------------------------------------
        updateGradedAssignment(id, gradeID, grade, message) {
            const vm = this;
            vm.classData = vm.classData.map(c => {
                const cls = c;
                if( cls.id === vm.slcdClass.id) {
                    cls[vm.slcdUserType + 'List'].map(u => {
                        const user = u;
                        if(vm.slcdClass.asgmtType === 'Activity') {
                            user['activity'].map(p => {
                                const proj = p;
                                if(proj.id === vm.slcdProjectID) {
                                    proj.map(a => {
                                        const asgmt = a;
                                        if(asgmt.id === id) {
                                            asgmt.grade_id = gradeID;
                                            asgmt.message = message;
                                        }
                                        return asgmt;
                                    })
                                }
                                return proj
                            })
                        } else {
                            user[vm.slcdCat.name.toLowerCase()].map(a => {
                                const asgmt = a;
                                if(asgmt.id === id) {
                                    asgmt.grade_id = gradeID
                                    asgmt.message = message;
                                }
                                return asgmt;
                            })
                        }
                        return user;
                    })
                }
                return cls;
            })

        },

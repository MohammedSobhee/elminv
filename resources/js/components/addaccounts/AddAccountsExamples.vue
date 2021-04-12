<template>
<div v-if="show">
    <hr class="my-2">
    <div>
        <button
            type="button"
            class="btn btn-sm btn-link btn-link-gray"
            :class="{'text-danger': hasErrors}"
            @click="viewSamples = !viewSamples">
            {{ viewSamples ? 'Hide' : 'View' }} Accepted CSV Format Upload Examples
            <i :class="[viewSamples ? 'fa-angle-up' : 'fa-angle-down']" class="fas fa-angle-down"></i>
        </button>
    </div>
    <div v-if="viewSamples" class="p-3 text-center">
        <span class="text-muted">Accepted CSV Format Upload Examples</span>
        <div class="row">
            <div class="col pt-4">
                <div class="text-primary pb-3 small">
                    Supplying email <a href="/files/csv_samples/supplying_email.csv" class="ml-2">[Download]</a>
                </div>
                <table class="table table-bordered small table-sample text-muted">
                    <thead>
                        <tr class="bg-white-gray" :class="{'table-sample-error': hasErrors }">
                            <th>First Name</th><th>Last Name</th><th>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>Mary</td><td>Carusso</td><td>mary@abcemail.com</td></tr>
                        <tr><td>Jane</td><td>Robertson</td><td>jane.robertson@gmail.com</td></tr>
                        <tr><td>John</td><td>Sampson</td><td>jsampson@coolschool.com</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="col pt-4">
                <div class="text-primary pb-3 small">
                    System generated usernames and passwords <a href="/files/csv_samples/system_generated.csv" class="ml-2">[Download]</a>
                </div>
                <table class="table table-bordered small table-sample text-muted">
                    <thead>
                        <tr class="bg-white-gray" :class="{'table-sample-error': hasErrors }">
                            <th>First Name</th><th>Last Name</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>Mary</td><td>Carusso</td></tr>
                        <tr><td>Jane</td><td>Robertson</td></tr>
                        <tr><td>John</td><td>Sampson</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="row">
            <div class="col pt-4">
                <div class="text-primary pb-3 small">
                    Supplying usernames <a href="/files/csv_samples/supplying_usernames.csv" class="ml-2">[Download]</a>
                </div>
                <table class="table table-bordered small table-sample text-muted">
                    <thead>
                        <tr class="bg-white-gray" :class="{'table-sample-error': hasErrors }">
                            <th>First Name</th><th>Last Name</th><th>Username</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>Mary</td><td>Carusso</td><td>mary.carusso98.schoolname</td></tr>
                        <tr><td>Jane</td><td>Robertson</td><td>jane.robertson-somethingƒ</td></tr>
                        <tr><td>John</td><td>Sampson</td><td>these-must-be-unique</td></tr>
                    </tbody>
                </table>
            </div>
            <div v-if="!class_id" class="col-lg-6 pt-4">
                <div class="text-primary pb-3 small">
                    Providing Class ID from Legend <a href="/files/csv_samples/providing_classid.csv" class="ml-2">[Download]</a>
                </div>
                <table class="table table-bordered small table-sample text-muted">
                    <thead>
                        <tr class="bg-white-gray" :class="{'table-sample-error': hasErrors }">
                            <th>First Name</th><th>Last Name</th><th>Username</th><th>Class ID</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>Mary</td><td>Carusso</td><td>mary.carusso98.schoolname</td><td>33</td></tr>
                        <tr><td>Jane</td><td>Robertson</td><td>jane.robertson-somethingƒ</td><td>28</td></tr>
                        <tr><td>John</td><td>Sampson</td><td>these-must-be-unique</td><td>47</td></tr>
                    </tbody>
                </table>
            </div>
            <div v-else class="col pt-4">
                <div class="text-primary pb-3 small">
                    Supplying email/username and password <a href="/files/csv_samples/supplying_passwords.csv" class="ml-2">[Download]</a>
                </div>
                <table class="table table-bordered small table-sample text-muted">
                    <thead>
                        <tr class="bg-white-gray" :class="{'table-sample-error': hasErrors }">
                            <th>First Name</th><th>Last Name</th><th>Email</th><th>Password</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>Mary</td><td>Carusso</td><td>mary@abcemail.com</td><td>3RrtY2200</td></tr>
                        <tr><td>Jane</td><td>Robertson</td><td>jane.robertson@gmail.com</td><td>Bdsp331!</td></tr>
                        <tr><td>John</td><td>Sampson</td><td>jsampson@coolschool.com</td><td>js33281p</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="row">
            <div class="col pt-4">
                <div class="text-primary pb-3 small">
                    Supplying email/username and password, with note (note optional) <a href="/files/csv_samples/supplying_passwords_note.csv" class="ml-2">[Download]</a>
                </div>
                <table class="table table-bordered small table-sample text-muted">
                    <thead>
                        <tr class="bg-white-gray" :class="{'table-sample-error': hasErrors}">
                            <th>First Name</th><th>Last Name</th><th>Email</th><th>Password</th><th>Note</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>Mary</td><td>Carusso</td><td>mary@abcemail.com</td><td>mary.carusso2284</td><td>Your password is firstname.lastname followed by your lunch code. E.g - mary.james3322</td></tr>
                        <tr><td>Jane</td><td>Robertson</td><td>jane.roberts@gmail.com</td><td>jane.roberts8276</td><td>Your password is firstname.lastname followed by your lunch code. E.g - mary.james3322</td></tr>
                        <tr><td>John</td><td>Sampson</td><td>jsampson@cls.com</td><td>john.sampson3911</td><td>Your password is firstname.lastname followed by your lunch code. E.g - mary.james3322</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</template>
<script>
export default {
    name: 'UploadAccountsExamples',
    props: {
        class_id: {
            type: Number,
            required: true
        },
        hasErrors: {
            type: Boolean,
            required: true
        },
        show: {
            type: Boolean,
            required: true
        }
    },
    data() {
        return {
            viewSamples: false
        }
    }
}
</script>

<template>
<div class="eduadmin form-label-status">
    <!-- Notification -->
    <notification :msg="notice" type="bootstrap" />

    <!-- Navigation -->
    <div class="row mt-4 mb-3">
        <div class="col-md-3 col-lg-3 col-xl-2">
            <a href="/eduadmin/create/school" class="btn btn-sm btn-primary">Create School <i class="fas fa-angle-right"></i></a>
        </div>
        <div class="col-md-7 col-lg-6 col-xl-7">
            <ul class="nav nav-eduadmin">
                <li class="nav-item" :class="{active: page === '' || page === 'edit-status'}">
                    <a class="nav-link" href="#" @click.prevent.stop="goto('edit-status')">Settings</a>
                </li>
                <li class="nav-item" :class="{active: page === 'edit-contact'}">
                    <a class="nav-link" href="#" @click.prevent.stop="goto('edit-contact')">Contacts</a>
                </li>
                <li class="nav-item" :class="{active: page === 'school-users'}">
                    <a class="nav-link" href="#" @click.prevent.stop="goto('school-users')">Members</a>
                </li>
                <li class="nav-item" :class="{active: page === 'upload-contract'}">
                    <a class="nav-link" href="#" @click.prevent.stop="goto('upload-contract')"><span>View </span>Contract</a>
                </li>
                <li class="nav-item" :class="{active: page === 'view-codes'}">
                    <a class="nav-link" href="#" @click.prevent.stop="goto('view-codes')"><span>View </span>Codes</a>
                </li>
            </ul>
        </div>
        <!-- Color Code -->
        <div class="col-12 col-md-2 col-lg-3 mt-4 mt-md-0 position-relative">
            <i v-if="codeStatus" class="fas fa-check color-code-status"></i>
            <multiselect
                v-model="selectedColorCode"
                :options="color_codes"
                placeholder="Color Code"
                track-by="id"
                label="value"
                :close-on-select="true"
                :show-labels="false"
                :allow_empty="true"
                class="multiselect-sm color-code-select"
                :selected="schoolData.contact.color_code"
                name="color_code"
                :searchable="false"
                @remove="removeColorCode"
                @select="saveColorCode">
                <template
                    v-for="slotName in ['option', 'singleLabel']"
                    :slot="slotName"
                    slot-scope="props">
                    <!-- eslint-disable-next-line vue/require-v-for-key -->
                    <span
                        :style="`display:block;background-color:${props.option.value};color:#fff;padding:2px`">
                        {{ props.option.label }}
                    </span>
                </template>
            </multiselect>
        </div>
    </div>

    <!-- Search forms -->
    <div class="form-wrapper form-wrapper-search p-2">
        <div class="row">
            <div class="col">
                <div class="form-group">
                    <label for="search-school" class="d-none">Search schools:</label>
                    <school-select
                        :options="schoolsList"
                        :selected="selectedSchool.id">
                    </school-select>
                </div>
            </div>
            <div class="col">
                <form action="/eduadmin/search/users" method="post">
                    <input type="hidden" name="_token" :value="csrf">
                    <div class="form-group">
                        <label for="search" class="d-none">Search Users:</label>
                        <input
                            id="search"
                            type="text"
                            name="search"
                            class="form-control"
                            :placeholder="search_term || 'Search Users'">
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- School Status -->
    <div v-if="!page || page === 'edit-status'">
        <hr id="edit-status" class="mb-3">
        <div class="row">
            <div class="col-md-6">
                <h4 class="pb-3">
                    School Status (ID: {{ schoolData.id }})
                    <span v-if="!schoolData.status" class="text-danger ml-2">Deactivated</span>
                </h4>
            </div>
            <div class="col-md-6 text-right">
                <label class="label-status" data-status="last_contacted_date" for="last_contacted_date">Last Contacted: </label>
                <date-input
                    id="last_contacted_date"
                    name="last_contacted_date"
                    class="d-inline-block ml-2 date-input-sm"
                    css_class="form-control-sm"
                    :value="schoolData.contact.last_contacted_date"
                    :emit="true"
                    @focused="datePlaceholder"
                    @changed="save" />
            </div>
        </div>
        <editor-input
            :id="school_id"
            post-url="/eduadmin/edit/school/note"
            :text="schoolData.contact.notes">
        </editor-input>

        <!-- School Status -->
        <div class="form-row mt-4">
            <div class="form-group" :class="`col-md-${schoolData.types.length+3}`">
                <label
                    :class="{ 'text-danger font-weight-bold': !schoolData.types.length }"
                    class="label-status"
                    data-status="school_type"
                    for="school_type">
                    School Class Types:
                </label>
                <multiselect
                    id="school_type"
                    v-model="schoolData.types"
                    :options="school_types"
                    track-by="class_type"
                    label="name"
                    name="school_type"
                    :multiple="true"
                    :show-labels="false"
                    :close-on-select="false"
                    :taggable="true"
                    :allow_empty="false"
                    class="multiselect-sm multiselect-taggable"
                    :searchable="false"
                    @remove="removeSchoolType"
                    @select="saveSchoolType">
                    <!-- <template slot="selection" slot-scope="{ values }">
                        <span v-if="values.length" class="multiselect__single">Selected {{ values.length }} Types</span>
                    </template> -->
                </multiselect>
            </div>
            <div class="form-group col-md-2">
                <label
                    class="label-status"
                    data-status="standards"
                    for="standards">
                    Standards:
                </label>
                <multiselect
                    id="standards"
                    v-model="schoolData.settings.standards"
                    placeholder="Select Standard"
                    :options="standards"
                    name="standards"
                    :show-labels="false"
                    :allow_empty="false"
                    class="multiselect"
                    :searchable="true"
                    @select="saveStandards">
                </multiselect>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-4">
                <label class="label-status" data-status="contract_sent_date" for="contract_sent_date">Contract Sent:</label>
                <date-input
                    id="contract_sent_date"
                    name="contract_sent_date"
                    :value="schoolData.settings.contract_sent_date"
                    :emit="true"
                    @focused="datePlaceholder"
                    @changed="save" />
            </div>
            <div class="form-group col-md-4">
                <label class="label-status" data-status="contract_received_date" for="contract_received_date">Contract Received:</label>
                <date-input
                    id="contract_received_date"
                    name="contract_received_date"
                    :value="schoolData.settings.contract_received_date"
                    :emit="true"
                    @focused="datePlaceholder"
                    @changed="save" />
            </div>
            <div class="form-group col-md-1">
                <label class="label-status" data-status="term" for="term">Term:</label>
                <input
                    id="term"
                    v-model="schoolData.settings.term"
                    type="text"
                    name="term"
                    class="form-control"
                    placeholder="Yrs"
                    @keyup.enter="save"
                    @change="save" />
            </div>
            <div class="form-group col-md-3">
                <label class="label-status" data-status="contract_expiration_date" for="contract_expiration_date"><span class="d-none d-lg-inline">Contract </span>Expiration Date:</label>
                <date-input
                    id="contract_expiration_date"
                    name="contract_expiration_date"
                    :value="schoolData.settings.contract_expiration_date"
                    :emit="true"
                    @focused="datePlaceholder"
                    @changed="save" />
            </div>
        </div>
        <div class="form-row pb-0 mb-4">
            <div class="col-md-12">
                <div class="form-check form-check-inline">
                    <input
                        id="payment_due"
                        v-model="schoolData.settings.payment_due"
                        type="checkbox"
                        class="form-check-input"
                        name="payment_due"
                        :value="schoolData.settings.payment_due ? 0 : 1"
                        @keyup.enter="save"
                        @change="save" />
                    <label for="payment_due" class="form-check-label" :class="{'text-dark': schoolData.settings.payment_due}">
                        Payment Due <span class="label-status" data-status="payment_due"></span></label>
                </div>
                <div class="form-check form-check-inline ml-lg-4">
                    <input
                        id="materials_sent"
                        v-model="schoolData.settings.materials_sent"
                        name="materials_sent"
                        type="checkbox"
                        class="form-check-input"
                        :value="schoolData.settings.materials_sent ? 0 : 1"
                        @keyup.enter="save"
                        @change="save" />
                    <label for="materials_sent" class="form-check-label" :class="{'text-dark': schoolData.settings.materials_sent}">
                        Materials Sent <span class="label-status" data-status="materials_sent"></span></label>
                </div>
                <div class="form-check form-check-inline form-check-secondary ml-lg-4">
                    <input
                        id="materials_paid"
                        v-model="schoolData.settings.materials_paid"
                        type="checkbox"
                        class="form-check-input"
                        name="materials_paid"
                        :value="schoolData.settings.materials_paid ? 0 : 1"
                        @keyup.enter="save"
                        @change="save" />
                    <label for="materials_paid" class="form-check-label" :class="{'text-secondary': schoolData.settings.materials_paid}">
                        Materials Paid <span class="label-status" data-status="materials_paid"></span></label>
                </div>
                <div class="form-check form-check-inline ml-lg-4">
                    <input
                        id="auto_renewal_sent"
                        v-model="schoolData.settings.auto_renewal_sent"
                        type="checkbox"
                        class="form-check-input"
                        name="auto_renewal_sent"
                        :value="schoolData.settings.auto_renewal_sent ? 0 : 1"
                        @keyup.enter="save"
                        @change="save" />
                    <label for="auto_renewal_sent" class="form-check-label" :class="{'text-dark': schoolData.settings.auto_renewal_sent}">
                        Auto Renewal Sent <span class="label-status" data-status="auto_renewal_sent"></span></label>
                </div>
                <div class="form-check form-check-inline ml-lg-4">
                    <input
                        id="auto_renewal_received"
                        v-model="schoolData.settings.auto_renewal_received"
                        type="checkbox"
                        class="form-check-input"
                        name="auto_renewal_received"
                        :value="schoolData.settings.auto_renewal_received ? 0 : 1"
                        @keyup.enter="save"
                        @change="save" />
                    <label for="auto_renewal_received" class="form-check-label" :class="{'text-dark': schoolData.settings.auto_renewal_received}">
                        Auto Renewal Received <span class="label-status" data-status="auto_renewal_received"></span></label>
                </div>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group form-group-secondary col-md-3">
                <label class="label-status" data-status="payment_received_date" for="payment_received_date">Payment Received<span class="d-none d-lg-inline"> Date:</span></label>
                <date-input
                    id="payment_received_date"
                    name="payment_received_date"
                    :value="schoolData.settings.payment_received_date"
                    :emit="true"
                    @focused="datePlaceholder"
                    @changed="save" />
            </div>
            <div class="form-group col-md-3">
                <label class="label-status" data-status="certified" for="certified">Certified:</label>
                <input
                    id="certified"
                    v-model="schoolData.settings.certified"
                    type="text"
                    name="certified"
                    class="form-control"
                    placeholder="Type"
                    @keyup.enter="save"
                    @change="save" />
            </div>
            <div class="form-group col-md-3">
                <label class="label-status" data-status="certified_date" for="certified_date">Certified Date:</label>
                <date-input
                    id="certified_date"
                    :value="schoolData.settings.certified_date"
                    name="certified_date"
                    :emit="true"
                    @focused="datePlaceholder"
                    @changed="save" />
            </div>
            <div class="form-group col-md-3">
                <label class="label-status" data-status="contest" for="contest">Contest:</label>
                <input
                    id="contest"
                    v-model="schoolData.settings.contest"
                    type="text"
                    name="contest"
                    class="form-control"
                    placeholder="Type"
                    @keyup.enter="save"
                    @change="save" />
            </div>
        </div>
        <div v-if="schoolData.id !== 30" class="form-row mt-4">
            <div class="form-group form-group-secondary col-md-5 offset-md-7 text-right align-items-end">
                <div class="form-check">
                    <!-- v-model="schoolData.status" -->
                    <input
                        id="school_status"
                        type="checkbox"
                        class="form-check-input"
                        name="school_status"
                        :value="schoolData.status ? 0 : 1"
                        @change="save" />
                    <label for="school_status" class="form-check-label" :class="{'text-dark': !schoolData.status}">
                        {{ schoolData.status ? 'Deactivate' : 'Reactivate' }} School
                        <span class="label-status" data-status="school_status"></span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- School Contacts -->
    <div v-if="page === 'edit-contact'">
        <hr id="edit-contact" class="mb-3">
        <div class="row">
            <div class="col-md-3">
                <h4 class="pb-3">Contact Information</h4>
            </div>
            <div class="col-md-9 text-right">
                <label class="label-status" data-status="last_contacted_date" for="last_contacted_date2">Last Contacted: </label>
                <date-input
                    id="last_contacted_date2"
                    name="last_contacted_date"
                    class="d-inline-block ml-2 date-input-sm"
                    css_class="form-control-sm"
                    :value="schoolData.contact.last_contacted_date"
                    :emit="true"
                    @focused="datePlaceholder"
                    @changed="save" />
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-1 mt-0">
                <label class="label-status" data-status="esa" for="esa">ESA</label>
                <input
                    id="esa"
                    v-model="schoolData.esa"
                    type="text"
                    name="esa"
                    class="form-control"
                    placeholder="ESA"
                    @keyup.enter="save"
                    @change="save" />
            </div>
            <div class="form-group col-md-6 mt-0">
                <label class="label-status" data-status="school_name" for="school_name">School Name</label>
                <input
                    id="school_name"
                    v-model="schoolData.name"
                    type="text"
                    name="school_name"
                    class="form-control"
                    placeholder="School Name"
                    @keyup.enter="save"
                    @change="save" />
            </div>
            <div class="form-group col-md-5 mt-0">
                <label class="label-status" data-status="school_district" for="school_district">School District</label>
                <input
                    id="school_district"
                    v-model="schoolData.district"
                    type="text"
                    name="school_district"
                    class="form-control"
                    placeholder="School District"
                    @keyup.enter="save"
                    @change="save" />
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-4">
                <label class="label-status" data-status="first_name" for="first_name">First Name</label>
                <input
                    id="first_name"
                    v-model="schoolData.contact.first_name"
                    type="text"
                    name="first_name"
                    class="form-control"
                    placeholder="First Name"
                    @keyup.enter="save"
                    @change="save" />
            </div>
            <div class="form-group col-md-4">
                <label class="label-status" data-status="last_name" for="last_name">Last Name</label>
                <input
                    id="last_name"
                    v-model="schoolData.contact.last_name"
                    type="text"
                    name="last_name"
                    class="form-control"
                    placeholder="Last Name"
                    @keyup.enter="save"
                    @change="save" />
            </div>
            <div class="form-group col-md-4">
                <label class="label-status" data-status="title" for="title">Title</label>
                <input
                    id="title"
                    v-model="schoolData.contact.title"
                    type="text"
                    name="title"
                    class="form-control"
                    placeholder="Title"
                    @keyup.enter="save"
                    @change="save" />
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label class="label-status" data-status="email" for="email">Email</label> <a :href="`mailto:${schoolData.contact.email}`"><i class="fas fa-envelope"></i></a>
                <input
                    id="email"
                    v-model="schoolData.contact.email"
                    type="text"
                    name="email"
                    class="form-control"
                    placeholder="Email"
                    @keyup.enter="save"
                    @change="save" />
            </div>
            <div class="form-group col-md-4">
                <label class="label-status" data-status="phone" for="phone">Phone</label> <a :href="`tel:${schoolData.contact.phone}`"><i class="fas fa-phone"></i></a>
                <input
                    id="phone"
                    v-model="schoolData.contact.phone"
                    type="text"
                    name="phone"
                    class="form-control"
                    placeholder="Phone"
                    @keyup.enter="save"
                    @change="save" />
            </div>
            <div class="form-group col-md-2">
                <label class="label-status" data-status="ext" for="ext">Ext</label>
                <input
                    id="ext"
                    v-model="schoolData.contact.extension"
                    type="text"
                    name="ext"
                    class="form-control"
                    placeholder="Ext"
                    @keyup.enter="save"
                    @change="save" />
            </div>
        </div>
        <div class="form-row pt-4">
            <div class="form-group col-md-3">
                <label
                    v-btooltip="{delay:1000, title: schoolData.contact.principal}"
                    class="label-status"
                    for="principal"
                    data-status="principal">Principal</label>
                <input
                    id="principal"
                    v-model="schoolData.contact.principal"
                    type="text"
                    name="principal"
                    class="form-control"
                    placeholder="Principal"
                    @keyup.enter="save"
                    @change="save" />
            </div>
            <div class="form-group col-md-3">
                <label
                    v-btooltip="{delay:1000, title: schoolData.contact.superintendent}"
                    class="label-status"
                    data-status="superintendent"
                    for="superintendent"
                    data-toggle="tooltip">Superintendent</label>
                <input
                    id="superintendent"
                    v-model="schoolData.contact.superintendent"
                    type="text"
                    name="superintendent"
                    class="form-control"
                    placeholder="Superintendent"
                    @keyup.enter="save"
                    @change="save" />
            </div>
            <div class="form-group col-md-6">
                <label
                    v-btooltip="{delay:1000, title: schoolData.contact.admin_contact}"
                    class="label-status"
                    data-status="admin_contact"
                    for="admin_contact">Admin Contact(s)</label>
                <input
                    id="admin_contact"
                    v-model="schoolData.contact.admin_contact"
                    type="text"
                    name="admin_contact"
                    class="form-control"
                    placeholder="Admin Contact(s)"
                    @keyup.enter="save"
                    @change="save" />
            </div>
        </div>
        <div class="pt-4">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label class="label-status" data-status="address1" for="address1">Address 1</label>
                    <input
                        id="address1"
                        v-model="schoolData.contact.address1"
                        type="text"
                        name="address1"
                        class="form-control"
                        placeholder="Address 1"
                        @keyup.enter="save"
                        @change="save" />
                </div>
                <div class="form-group col-md-6">
                    <label class="label-status" data-status="address2" for="address2">Address 2</label>
                    <input
                        id="address2"
                        v-model="schoolData.contact.address2"
                        type="text"
                        name="address2"
                        class="form-control"
                        placeholder="Address 2"
                        @keyup.enter="save"
                        @change="save" />
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-8">
                    <label class="label-status" data-status="city" for="city">City</label>
                    <input
                        id="city"
                        v-model="schoolData.contact.city"
                        type="text"
                        name="city"
                        class="form-control"
                        placeholder="City"
                        @keyup.enter="save"
                        @change="save" />
                </div>
                <div class="form-group col-md-1">
                    <label class="label-status" data-status="state" for="state">State</label>
                    <input
                        id="state"
                        v-model="schoolData.contact.state"
                        type="text"
                        name="state"
                        class="form-control"
                        placeholder="State"
                        @keyup.enter="save"
                        @change="save" />
                </div>
                <div class="form-group col-md-3">
                    <label class="label-status" data-status="zip" for="zip">Zip</label>
                    <input
                        id="zip"
                        v-model="schoolData.contact.zip"
                        type="text"
                        name="zip"
                        class="form-control"
                        placeholder="Zip Code"
                        @keyup.enter="save"
                        @change="save" />
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-12 mt-0">
                    <label class="label-status" data-status="country" for="country">Country</label>
                    <select
                        id="country"
                        v-model="schoolData.contact.country"
                        name="country"
                        class="custom-select"
                        @keyup.enter="save"
                        @change="save">
                        <option
                            v-for="(country, index) in countries"
                            :key="index"
                            :value="country"
                            :selected="country === schoolData.contact.country">
                            {{ country }}
                        </option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- School Users -->
    <div v-if="page === 'school-users'">
        <hr id="school-users" class="mb-3">
        <div v-if="school_users.length > 0">
            <h4 class="pb-3">All members of {{ schoolData.name }}</h4>
            <edit-member
                :users="school_users"
                :editing_school="school_id"
                :admin_school="admin_school"
                :enable_search="true"
                class="mt-3">
            </edit-member>
        </div>
        <div v-else class="row align-items-end">
            <div class="col-4">
                <h4 class="mt-4 text-muted">{{ schoolData.name }} has no members.</h4>
                <div><small class="text-muted">This school has no members so it's safe to delete.</small></div>
            </div>
            <!-- Delete school -->
            <div class="col-3 position-relative">
                <a href="#" class="btn btn-sm btn-danger pl-1" @click="deleteConfirm = !deleteConfirm"><i class="fas fa-exclamation-triangle mr-2"></i> Delete school</a>
                <popover-dialogue
                    v-if="deleteConfirm"
                    :id="school_id"
                    style="bottom:3rem;right:8rem"
                    @answer="deleteSchool">
                    Are you sure? This cannot be undone.
                </popover-dialogue>
            </div>
        </div>
    </div>

    <!-- Upload/View Contract -->
    <div
        v-if="page === 'upload-contract'"
        ref="uploadContract"
        v-stopFileDrag
        class="position-relative"
        :style="!schoolData.settings.contract_file && 'height:60vh'"
        @dragenter="dragging = true">
        <!-- Dragging Indicator -->
        <div
            v-show="dragging"
            ref="fileDrop"
            class="form-wrapper-file-dragging"
            @dragover.prevent
            @drop.prevent="uploadContract">
            <span>Drop file</span>
        </div>
        <template v-if="!schoolData.settings.contract_file">
            <hr id="view-codes" class="mb-2">
            <h4 class="pb-3">View/Upload Contract</h4>
        </template>
        <div
            id="fileUpload"
            ref="formWrapper"
            class="form-wrapper-file form-upload-display px-2 py-1 my-3"
            @drop.prevent="uploadContract"
            @dragover.prevent
            @dragenter="dragging = true">
            <div class="flex-row row align-items-center">
                <div class="col-md-8">
                    <span class="btn btn-sm btn-light btn-file">
                        <span>Browse to upload</span>
                        <input
                            class="form-control-file"
                            type="file"
                            accept="application/pdf"
                            @change="uploadContract" />
                    </span>
                     <span class="text-gray small d-none d-lg-inline-block p-2">Or drag and drop a <strong>PDF</strong> onto the page</span>
                </div>
                <div v-if="schoolData.settings.contract_file" class="col-md-4 text-right">
                    <button type="button" class="btn btn-sm btn-light" @click="clearContract">Remove</button>
                    <a
                        class="btn btn-sm btn-light"
                        :download="`${schoolData.name} Contract`"
                        :href="'/contracts/' + schoolData.settings.contract_file"
                        target="_blank"
                        @click.stop="">
                        Download Contract
                    </a>
                </div>
            </div>

            <!-- Upload indicator -->
            <div class="progress-eduadmin-wrapper">
                <div
                    v-show="uploadPercentage && uploadPercentage !== 100"
                    class="progress">
                    <div
                        class="progress-bar progress-bar-striped progress-bar-animated"
                        role="progressbar"
                        :aria-valuenow="uploadPercentage"
                        aria-valuemin="0"
                        aria-valuemax="100"
                        :style="'width:' + uploadPercentage + '%'">
                    </div>
                </div>
            </div>
            <!-- End Upload indicator -->
        </div>

        <!-- View Contract -->
        <template v-if="schoolData.settings.contract_file">
            <div
                v-if="schoolData.settings.contract_file.toLowerCase().indexOf('pdf') !== -1"
                id="pdf"
                v-pdfViewer:url="'/contracts/' + schoolData.settings.contract_file"
                class="gb-assignment-pdfviewer">
            </div>
        </template>
    </div>

    <!-- View Codes -->
    <template v-if="page === 'view-codes'">
        <p class="mt-4">School Administrators are created using the <strong>school code.</strong> They are integral to a well-functioning school. They can optionally decide to be a teacher as well when activating their account.</p>
        <hr id="view-codes" class="mb-2">
        <h4 class="pb-3">View Codes</h4>
        <view-codes
            :host="host"
            :class_type="0"
            :school_code="schoolData.school_code"
            :teacher_code="schoolData.teacher_code">
        </view-codes>
    </template>
</div>
</template>

<script>
import notify from '../../mixins/notify';
import { elementExists } from '../../functions/utils';
import apiRequest from '../../functions/apiRequest';
import { pdfViewer, PDFObject } from '../../directives/pdfViewer';
import stopFileDrag from '../../directives/stopFileDrag';

export default {
    name: 'EditSchool',

    directives: {
        pdfViewer,
        stopFileDrag
    },

    components: {
        Multiselect: () => import(/* webpackChunkName:"vue-multiselect" */ 'vue-multiselect')
    },
    mixins: [notify],

    props: {
        schools: {
            type: Array,
            required: true
        },
        school_id: {
            type: Number,
            required: true
        },
        school: {
            type: Object,
            default: () => {}
        },
        school_types: {
            type: Array,
            required: true
        },
        school_users: {
            type: Array,
            required: true
        },
        host: {
            type: String,
            required: true
        },
        color_codes: {
            type: Array,
            required: true
        },
        countries: {
            type: Array,
            required: true
        },
        standards: {
            type: Array,
            required: true
        },
        search_term: {
            type: String,
            required: true
        },
        admin_school: {
            type: Number,
            default: 0
        }
    },

    data() {
        return {
            selectedColorCode: this.color_codes.find(obj => obj.id === this.school.contact.color_code),
            selectedSchool: this.schools.find(obj => obj.id === this.school_id),
            schoolData: this.school,
            schoolsList: this.schools,
            deleteConfirm: false,
            codeStatus: false,
            page: '',
            uploadPercentage: 0,
            dragging: false,
            contractFile: '',
            placeholderID: 0,
            csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    },

    mounted() {
        this.cookie = this.getCookie();
        if(this.cookie.el === 'school-users') {
            this.goto(this.cookie.el);
        }

        if(!this.schoolData.types.length) {
            this.goto('edit-status');
            this.notify(`This school's allowed class types have not been set. Please set them now.`, 'danger', 0);
        }
    },

    methods: {
        //
        // Go to page
        // --------------------------------------------------------------------------
        goto(el) {
            this.page = el;
            this.addFileDropSize();
            this.setCookie(this.cookie, { el });
        },
        //
        // Show Placeholder
        // --------------------------------------------------------------------------
        datePlaceholder(id) {
            document.getElementById(id).setAttribute('placeholder', 'Month Day, Year');
        },
        //
        // Save Edits
        // --------------------------------------------------------------------------
        save(event) {
            const vm = this;
            const et = event.target
            let request = {
                school_id: vm.school_id
            }
            request[et.name] = et.value;

            // If paid date is set, set payment due 0
            if(et.name === 'payment_received_date' && et.value) {
                vm.schoolData.settings.payment_due = 0;
                request['payment_due'] = 0;
            }

            apiRequest('/eduadmin/edit/school', request).then(response => {
                // Show error (mainly for attempting to deactivate admin school)
                if(response.status === 503) {
                    vm.notify(response.data.error, 'danger');

                } else {
                    // Update label status
                    et.name = et.name === 'remove_school_type' ? 'school_type' : et.name;
                    const el = document.querySelector(`.label-status[data-status="${et.name}"]`);
                    el.classList.add('saved-data');

                    // Payment due change
                    if(et.name === 'payment_due') {
                        vm.schoolsList.find(obj => obj.id === vm.school_id).payment_due = +request.payment_due;
                    }
                    // Status change
                    if(et.name === 'school_status') {
                        const status = +request.school_status
                        // Update school status display
                        vm.schoolData.status = status;
                        vm.schoolsList.find(obj => obj.id === vm.school_id).status = status;
                        // Move deactivated to bottom
                        if(!status) {
                            vm.schoolsList.push(...vm.schoolsList.splice(
                                vm.schoolsList.findIndex(obj => obj.id === vm.school_id), 1
                            ))
                        }
                    }
                }
            });
        },
        //
        // Save Standard Selection
        // --------------------------------------------------------------------------
        saveStandards(value) {
            const event = {
                target: {
                    name: 'standards',
                    value: value
                }
            }
            this.save(event);
        },
        //
        // Save School Type
        // --------------------------------------------------------------------------
        saveSchoolType(value) {
            const event = {
                target: {
                    name: 'school_type',
                    value: value.class_type
                }
            }
            this.save(event);
        },
        removeSchoolType(value) {
            const event = {
                target: {
                    name: 'remove_school_type',
                    value: value.class_type
                }
            }
            this.save(event);
        },
        //
        // Save Color Code
        // --------------------------------------------------------------------------
        saveColorCode(value) {
            apiRequest('/eduadmin/edit/school', {
                school_id: this.school_id,
                color_code: value.id
            }).then(() => {
                this.codeStatus = true;
            })
        },
        removeColorCode() {
            this.saveColorCode({id: 0})
        },
        //
        // Delete School
        // --------------------------------------------------------------------------
        deleteSchool(id) {
            if(id !== 'cancel')
                window.location.href=`/eduadmin/delete/school/${id}`;
            else
                this.deleteConfirm = false;
        },
        //
        // Select School
        // --------------------------------------------------------------------------
        selectSchool(value) {
            this.selectedSchool = value.id;
        },
        //
        // File Drop methods
        // --------------------------------------------------------------------------
        addFileDropSize() {
            const vm = this;
            if(vm.page === 'upload-contract') {
                elementExists('#fileUpload').then(() => {
                    // Set height of file drop overlay to upload contract wrapper div
                    vm.$refs.fileDrop.style.height = vm.$refs.uploadContract.offsetHeight + 'px';
                    vm.$refs.fileDrop.style.width = vm.$refs.uploadContract.offsetWidth + 'px';
                });
            }
        },
        //
        // Upload / Clear Contract
        // --------------------------------------------------------------------------
        uploadContract(event) {
            const vm = this;
            const input = event.dataTransfer || event.target;
            const file = input.files[0];
            let formdata = new FormData();
            formdata.append('school_id', vm.school_id);

            if (typeof input !== 'undefined' && typeof file !== 'undefined') {
                if (!/pdf/i.test(file.name)) {
                    // Generate file type error.
                    vm.dragging = false;
                    vm.notify('PDF files only. Incompatible files have been skipped.', 'danger');
                    return;
                } else {
                    // Add files to formData
                    formdata.append('file', file);
                }
            }

            apiRequest('/eduadmin/edit/school', formdata, {
                onUploadProgress: event => {
                    vm.uploadPercentage = parseInt(
                        Math.round((event.loaded * 100) / event.total), 10
                    );
                }
            }).then(response => {
                vm.dragging = false;
                // Assign school data file location response
                vm.schoolData.settings.contract_file = response.contract_file;
                PDFObject.embed(`/contracts/${response.contract_file}`, '#pdf', vm.pdfOpenParams);
                vm.notify(response.success);
            });
        },
        clearContract() {
            const request = {
                school_id: this.school_id,
                contract_file: 0
            }
            apiRequest('/eduadmin/edit/school', request).then(() => {
                PDFObject.embed('', '#pdf', this.pdfOpenParams);
                this.notify('Successfully cleared contract.');
            })
        }
    }
};
</script>

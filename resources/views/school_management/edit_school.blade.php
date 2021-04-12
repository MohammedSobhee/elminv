<hr class="mb-3 mt-5">
    {{-- <div style="background-color:{{$school->contact->color_code}};height:1rem"></div> --}}
    <form method="post" action="/eduadmin/edit/school" id="form-createschool" class="form-school form-edit">
        {{ csrf_field() }}
        <div class="row pb-2">
            <div class="col-md-3">
            <vue-select
                :options="{{ json_encode($color_codes) }}"
                placeholder="Color Code"
                id_by="id"
                :allow_empty="true"
                css_class="multiselect-sm"
                :selected="{{ json_encode($school->contact->color_code) }}"
                name="color_code"
                :searchable="false"
                :colors="true"
                label_by="value">
            </vue-select>
            </div>
            <div class="col-md-9 text-right">
                <input type="submit" name="submit" value="Update School" class="btn btn-sm btn-light">
            </div>
        </div>

        <h4 class="pt-3 pb-2">School Status</h4>
        <div class="form-row">
            <div class="form-group col-md-4">
                <label for="contract_sent_date">Contract Sent:</label>
                <date-input name="contract_sent_date" :value="{{ json_encode($school->settings->contract_sent_date) }}" />
            </div>
            <div class="form-group col-md-4">
                <label for="contract_received_date">Contract Received:</label>
                <date-input name="contract_received_date" :value="{{ json_encode($school->settings->contract_received_date) }}" />
            </div>
            <div class="form-group col-md-1">
                <label for="term">Term: </label>
                <input type="text" name="term" id="term" class="form-control" placeholder="Yrs" value="{{ $school->settings->term }}" />
            </div>
            <div class="form-group col-md-3">
                <label for="contract_expiration_date">Contract Expiration Date:</label>
                <date-input name="contract_expiration_date" :value="{{ json_encode($school->settings->contract_expiration_date) }}" />
            </div>
        </div>
        <div class="form-row pb-0 mb-4">
            <div class="col-md-12">
                <div class="form-check form-check-inline">
                    <input type="checkbox" class="form-check-input" name="payment_due" id="payment_due" @if($school->settings->payment_due)checked @endif/>
                    <label for="payment_due" class="form-check-label @if($school->settings->payment_due)text-dark @endif">Payment Due</label>
                </div>
                {{-- <div class="form-check form-check-inline form-check-secondary ml-2">
                    <input type="checkbox" class="form-check-input" name="payment_received" id="payment_received" @if($school->settings->payment_received)checked @endif/>
                    <label for="payment_received" class="form-check-label @if($school->settings->payment_received)text-dark @endif">Payment Received</label>
                </div> --}}
                <div class="form-check form-check-inline ml-4">
                    <input type="checkbox" class="form-check-input" name="materials_sent" id="materials_sent" @if($school->settings->materials_sent)checked @endif/>
                    <label for="materials_sent" class="form-check-label @if($school->settings->materials_sent)text-dark @endif">Materials Sent</label>
                </div>
                <div class="form-check form-check-inline form-check-secondary ml-4">
                    <input type="checkbox" class="form-check-input" name="materials_paid" id="materials_paid" @if($school->settings->materials_paid)checked @endif/>
                    <label for="materials_paid" class="form-check-label @if($school->settings->materials_paid)text-secondary @endif">Materials Paid</label>
                </div>
                <div class="form-check form-check-inline ml-4">
                    <input type="checkbox" class="form-check-input" name="auto_renewal_sent" id="auto_renewal_sent" @if($school->settings->auto_renewal_sent)checked @endif/>
                    <label for="auto_renewal_sent" class="form-check-label @if($school->settings->auto_renewal_sent)text-secondary @endif">Auto Renewal Sent</label>
                </div>
                <div class="form-check form-check-inline ml-4">
                    <input type="checkbox" class="form-check-input" name="auto_renewal_received" id="auto_renewal_received" @if($school->settings->auto_renewal_received)checked @endif/>
                    <label for="auto_renewal_received" class="form-check-label @if($school->settings->auto_renewal_received)text-secondary @endif">Auto Renewal Received</label>
                </div>
            </div>
        </div>
        <div class="form-row">
           <div class="form-group form-group-secondary col-md-3">
                <label for="payment_received_date">Payment Received Date:</label>
                <date-input name="payment_received_date" :value="{{ json_encode($school->settings->payment_received_date) }}" />
            </div>
            <div class="form-group col-md-3">
                <label for="certified">Certified:</label>
                <input type="text" name="certified" id="certified" class="form-control" placeholder="Type" value="{{ $school->settings->certified }}" />
            </div>
            <div class="form-group col-md-3">
                <label for="certified_date">Certified Date:</label>
                <date-input name="certified_date" :value="{{ json_encode($school->settings->certified_date) }}" />
            </div>
            <div class="form-group col-md-3">
                <label for="contest">Contest:</label>
                <input type="text" name="contest" id="contest" class="form-control" placeholder="Type" value="{{ $school->settings->contest }}" />
            </div>
            {{-- <div class="form-group col-md-3">
                <label for="contract_expiration_date">Expiration Date:</label>
                <date-input name="contract_expiration_date" :value="{{ json_encode($school->settings->contract_expiration_date) }}" />
            </div> --}}
        </div>

        <div id="edit-contact"></div>
        <h4 class="pt-5 pb-2">Contact Information</h4>
        <div class="form-row">
            <div class="form-group col-md-6 mt-0">
                <label for="school_name">School Name</label>
                <input type="text" name="school_name" id="school_name" class="form-control" placeholder="School Name" value="{{ $school->name }}" />
            </div>
            <div class="form-group col-md-6 mt-0">
                <label for="school_district">School District</label>
                <input type="text" name="school_district" id="school_district" class="form-control" placeholder="School District" value="{{ $school->district }}" />
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="first_name">First Name</label>
                <input type="text" name="first_name" id="first_name" class="form-control" placeholder="First Name" value="{{ $school->contact->first_name }}"/>
            </div>
            <div class="form-group col-md-6">
                <label for="last_name">Last Name</label>
                <input type="text" name="last_name" id="last_name" class="form-control" placeholder="Last Name" value="{{ $school->contact->last_name }}"/>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for ="email">Email</label> <a href="mailto:{{ $school->contact->email }}"><i class="fas fa-envelope"></i></a>
                <input type="text" name="email" id="email" class="form-control" placeholder="Email" value="{{ $school->contact->email }}" />
            </div>
            <div class="form-group col-md-4">
                <label for="phone">Phone</label> <a href="tel:{{ $school->contact->phone }}"><i class="fas fa-phone"></i></a>
                <input type="text" name="phone" id="phone" class="form-control" placeholder="Phone" value="{{ $school->contact->phone }}" />
            </div>
            <div class="form-group col-md-2">
                <label for="ext">Ext</label>
                <input type= "text" name="ext" id="ext" class="form-control" placeholder="Ext" value="{{ $school->contact->extension }}"/>
            </div>
        </div>
        <div class="form-row pt-4">
            <div class="form-group col-md-3">
                <label for="principal" data-toggle="tooltip" title="{{ $school->contact->principal }}">Principal</label>
                <input type="text" name="principal" id="principal" class="form-control" placeholder="Principal" value="{{ $school->contact->principal }}" />
            </div>
            <div class="form-group col-md-3">
                <label for="superintendent" data-toggle="tooltip" title="{{ $school->contact->superintendent }}">Superintendent</label>
                <input type="text" name="superintendent" id="superintendent" class="form-control" placeholder="Superintendent" value="{{ $school->contact->superintendent }}" />
            </div>
            <div class="form-group col-md-6">
                <label for="admin_contact" data-toggle="tooltip" title="{{ $school->contact->admin_contact }}">Admin Contact(s)</label>
                <input type="text" name="admin_contact" id="admin_contact" class="form-control" placeholder="Admin Contact(s)" value="{{ $school->contact->admin_contact }}" />
            </div>
        </div>
        <div class="pt-4">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="address1">Address 1</label>
                    <input type="text" name="address1" id="address1" class="form-control" placeholder="Address 1" value="{{ $school->contact->address1 }}" />
                </div>
                <div class="form-group col-md-6">
                    <label for="address2">Address 2</label>
                    <input type="text" name="address2" id="address2" class="form-control" placeholder="Address 2" value="{{ $school->contact->address2 }}" />
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-8">
                    <label for="city">City</label>
                    <input type="text" name="city" id="city" class="form-control" placeholder="City" value="{{ $school->contact->city }}" />
                </div>
                <div class="form-group col-md-1">
                    <label for="state">State</label>
                    <input type="text" name="state" id="state" class="form-control" placeholder="State" value="{{ $school->contact->state }}" />
                </div>
                <div class="form-group col-md-3">
                    <label for="zip">Zip</label>
                    <input type="text" name="zip" id="zip" class="form-control" placeholder="Zip Code" value="{{ $school->contact->zip }}"/>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-12 mt-0">
                    <label for="country" class="">Country</label>
                    <select name="country" id="country" class="custom-select">
                        @foreach ($countries as $value)
                        <option value="{{ $value }}" @if ($value == $school->contact->country)
                            selected="selected"
                            @endif
                            >{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-12">
                <label for="school_type">School Type: </label>
                <select name="school_type" class="custom-select">
                    <option value=1>LOADED FROM DB</option>
                </select>
            </div>
        </div>
        <input type="hidden" name="school_id" value="{{ $school_id }}">
        <div class="text-right"><input type="submit" name="submit" value="Update School" class="btn btn-sm btn-light mt-2"></div>
    </form>

     public function worksheet(Request $request, $id = '') {
        $g = Worksheet::find($id);
        $groups = $g->groups()->join('worksheet_form_fields','worksheet_form_field_groups.form_field_id','worksheet_form_fields.id')->select('worksheet_form_field_groups.worksheet_id','worksheet_form_field_groups.form_field_id','worksheet_form_fields.heading','worksheet_form_fields.question','worksheet_form_fields.description','worksheet_form_fields.value','worksheet_form_fields.type','worksheet_form_fields.display_size')->orderBy('worksheet_form_field_groups.order')->get();

        return view('worksheets.worksheet', [
            'title' => $g->title,
            'wid' => $g->id,
            'initial_ws_data' => json_encode($groups)
        ]);
     }

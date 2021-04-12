<?php

        if(preg_match("/^\s*data:([a-z]+\/[a-z]+(;[a-z\-]+\=[a-z\-]+)?)?(;base64)?,[a-z0-9\!\$\&\'\,\(\)\*\+\,\;\=\-\.\_\~\:\@\/\?\%\s]*\s*$/i", $request->input('answer'))) {
            $image = $request->input('answer');
            $name = time().'.' . explode('/', explode(':', substr($image, 0, strpos($image, ';')))[1])[1];
            $uploads_path = public_path('uploads/'. date('Y'));
            \File::isDirectory($uploads_path) or \File::makeDirectory($uploads_path, 0775, true, true);
            \Image::make($request->input('answer'))->save($uploads_path . '/'.$name);
            $answer = $uploads_path . '/' . $name;
        }

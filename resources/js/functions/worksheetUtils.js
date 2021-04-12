// If data has been edited by other and field isn't busy saving
const shouldNotify = (fieldStatus, response, userID) => {
    if (fieldUpdated(response, userID) && !fieldSaving(fieldStatus)) return true
}


const fieldUpdated = (response, userID) => {
    return response.findIndex(obj => obj.settings.last_updated_by === userID) === -1;
};

const fieldSaving = fieldStatus => {
    return Object.values(fieldStatus).find(value => value === 'Saving...') !== undefined
}

// Reduce to only needed fields, extract the differences
const compareAnswers = (existing, incoming) => {
    const existingAnswers = reduceToIDAnswer(existing);
    const incomingAnswers = reduceToIDAnswer(incoming);
    return incomingAnswers.filter(incObj =>
        existingAnswers.some(exObj =>
            incObj.form_field_id === exObj.form_field_id && incObj.answer !== exObj.answer)
    );
}

const reduceToIDAnswer = array => {
    return array
        .flatMap(group => group.fields)
        .map(field => ['form_field_id', 'answer'].reduce((obj, props) => {
            obj[props] = field[props];
            return obj;
        }, {}));
}

export { shouldNotify, compareAnswers };

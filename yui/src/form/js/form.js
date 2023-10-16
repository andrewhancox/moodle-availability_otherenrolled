/**
 * JavaScript for form editing completion conditions.
 *
 * @module moodle-availability_otherenrolled-form
 */
M.availability_otherenrolled = M.availability_otherenrolled || {};

/**
 * @class M.availability_otherenrolled.form
 * @extends M.core_availability.plugin
 */
M.availability_otherenrolled.form = Y.Object(M.core_availability.plugin);

/**
 * Initialises this plugin.
 *
 * @method initInner
 * @param {Array} courses Array of objects containing courseid => name
 */
M.availability_otherenrolled.form.initInner = function (courses) {
    this.courses = courses;
};

M.availability_otherenrolled.form.getNode = function (json) {
    // Create HTML structure.
    var html = '<span class="col-form-label p-r-1"> ' + M.util.get_string('title', 'availability_otherenrolled') + '</span>' +
        ' <span class="availability-group form-group"><label>' +
        '<span class="accesshide">' + M.util.get_string('label_course', 'availability_otherenrolled') + ' </span>' +
        '<select class="custom-select" name="course" title="' + M.util.get_string('label_course', 'availability_otherenrolled') +
        '">' +
        '<option value="0">' + M.util.get_string('choosedots', 'moodle') + '</option>';
    for (var i = 0; i < this.courses.length; i++) {
        var course = this.courses[i];
        // String has already been escaped using format_string.
        html += '<option value="' + course.id + '">' + course.name + '</option>';
    }

    html += '</select></label>';
    var node = Y.Node.create('<span class="form-inline">' + html + '</span>');

    // Set initial values.
    if (json.course !== undefined &&
        node.one('select[name=course] > option[value=' + json.course + ']')) {
        node.one('select[name=course]').set('value', '' + json.course);
    }

    // Add event handlers (first time only).
    if (!M.availability_otherenrolled.form.addedEvents) {
        M.availability_otherenrolled.form.addedEvents = true;
        var root = Y.one('.availability-field');
        root.delegate('change', function () {
            // Whichever dropdown changed, just update the form.
            M.core_availability.form.update();
        }, '.availability_otherenrolled select');
    }

    return node;
};

M.availability_otherenrolled.form.fillValue = function (value, node) {
    value.course = parseInt(node.one('select[name=course]').get('value'), 10);
};

M.availability_otherenrolled.form.fillErrors = function (errors, node) {
    var courseid = parseInt(node.one('select[name=course]').get('value'), 10);
    if (courseid === 0) {
        errors.push('availability_otherenrolled:error_selectcourseid');
    }
};

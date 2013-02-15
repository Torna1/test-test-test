window.wb5_teacher = {
    editors_list: [], // list of codemirror objects 
    board_name: 'tests',
    is_changed_from_socket: false,
    refresh_history: [], // holds the history when the page is refreshed (all history, when does not exist a client to help)
    current_tabs: [], // holds all created tabs of this board
    history_from_friend: [], // holds the history sent by a client that is on the same course
    stored_test_html: [], // storing html data that is comming from ajax for repeated uses
    student_tests: [],
    
    init: function() {
        // bind elements events
        this.bindEvents();
    },
    bindEvents: function() {
        // set tab events: close tab, switch tab, rename tab
        this.setTabEvents();
        jQuery('#add_test_btn').unbind('click');
        jQuery('#add_test_btn').click(function() {
            window.wb5_teacher.prepareTestModal();
        });
        
        jQuery('#run_tests').unbind('click');
        jQuery('#run_tests').click(function() {
            window.wb5_teacher.runPreparedTests();
        });
    },
    runPreparedTests: function() {
        var students = [];
        var test = jQuery('#prepared_tests').val();
        if(test == '') {
            jQuery('#error_modal_msg').html('Please choose a test.');
            jQuery('#error_modal').modal();
            return;
        }
        var checked_student = jQuery('input:checkbox[class=users_for_tests]:checked');
        if(checked_student.length == 0) {
            jQuery('#error_modal_msg').html('Please choose at least a student.');
            jQuery('#error_modal').modal();
            return;
        }
        for (var i = 0; i < checked_student.length; i++) {
            students.push(jQuery(checked_student[i]).attr('value'));
        }
        
        this.sendTestToStudents(test, students, jQuery('#prepared_tests option:selected').text());
        this.createTestTabsAtTeacher(test, students);
    },
    // create the test locally at teachers desk so he can see the progress
    createTestTabsAtTeacher: function(test, checked_student) {
        jQuery('#test_modal').modal('hide');
        for (var i = 0; i < checked_student.length; i++) {
            this.createTeacherTest(test, window.board_manager.current_users[checked_student[i]]);
        }
    },
    createTeacherTest: function(test, student_data, caller, test_status) {
        var unique_id = student_data.hash+'_'+test;
        if(jQuery('#file_name_'+unique_id).length > 0) {
            return false;
        }
        
        if(this.student_tests[student_data.hash] === undefined) {
            this.student_tests[student_data.hash] = [];
        }
        this.student_tests[student_data.hash].push(test);
        this.current_tabs.push(unique_id);
        var test_name = jQuery('#prepared_tests option[value='+test+']').text();
        jQuery('#wb5_items_tabs').append('<div class="tab_div_wb5" data-sheet-id="'+unique_id+'"><span id="file_name_'+unique_id+'">'+test_name+' ('+student_data.f_name+' '+student_data.l_name+')</span> <sup><a href="javascript:;" data-test-hash="'+test+'" data-user-hash="'+student_data.hash+'" class="delete_teacher_test_teacher">x</a></sup></div>');
        if(window.wb5_teacher.stored_test_html[test] !== undefined) {
            window.wb5_teacher.setTabBindings(window.wb5_teacher.stored_test_html[test], unique_id, student_data.hash, test);
        } else {
            jQuery.ajax({
                url: "ajax",
                data: "todo=teacher_test&test_hash="+test,
                type: "get",
                async: false,
                beforeSend: function() {
                    // loadior here
                },
                success: function(data) {
                    window.wb5_teacher.stored_test_html[test] = data;
                    window.wb5_teacher.setTabBindings(data, unique_id, student_data.hash, test);
                    if(test_status !== undefined) {
                        window.wb5_teacher.setStudentTestStatus({ test_status: { test_status: test_status, test_hash: test }, student_hash: student_data.hash}, 'history');
                    }
                }
            });
        }
    },
    sendTestToStudents: function(test, students, test_name) {
        window.socket_object.emit('send_test_to_students', {
            test: test, 
            test_name: test_name, 
            students: students
        });
    },
    prepareTestModal: function() {
        var html = '';
        var current_users = window.board_manager.current_users;
        for(var item in current_users) {
            html += '<input type="checkbox" class="users_for_tests" id="for_'+item+'" value="'+item+'" /> <label style="display:inline;" for="for_'+item+'">' + current_users[item].f_name + ' ' + current_users[item].l_name + '</label><br />';
        }
        jQuery('#test_student_list').html(html);
        jQuery('#test_modal').modal();
    },
    setTabEvents: function() {
        // when tab's x is clicked
        this.bindDeleteTabEvent();
        
        // add blank tab
        jQuery('#add_text_sheet').click(function() {
            // generating unique id
            var unique_id = jQuery('#texts_list').val();
            var tab_name = jQuery('#texts_list option:selected').text();
            window.wb5.createTab(unique_id, tab_name, undefined, 'text');
        });
    },
    bindDeleteTabEvent:function(sheet_id, caller) {
        jQuery('.delete_teacher_test_teacher').unbind('click'); // unbind click from these elements to avoid multiplication of events
        jQuery('.delete_teacher_test_teacher').click(function(){
            var sheet_id = jQuery(this).parent().parent().attr('data-sheet-id');
            window.wb5_teacher.deleteTab(sheet_id, this);
        });
    },
    deleteTab: function(sheet_id, click_object) {
        var test_hash = jQuery(click_object).attr('data-test-hash');
        var user_hash = jQuery(click_object).attr('data-user-hash');
        window.socket_object.emit('wb5_tab_delete', {
            test_hash: test_hash,
            user_hash: user_hash
        });
        
        var index = this.current_tabs.indexOf(sheet_id);
        this.current_tabs.splice(index, 1);
        jQuery('#file_name_'+sheet_id).parent().remove(); // delete the tab
        jQuery('#board_item_'+sheet_id).remove();
        if(jQuery('.active_wp5_tab').length == 0) {
            var last_tab_sheet_id = jQuery(jQuery('.tab_div_wb5')[jQuery('.tab_div_wb5').length-1]).attr('data-sheet-id');
            window.wb5_teacher.switchTab(last_tab_sheet_id);
        }
    },
    // switch tabs when clicked
    bindTabSwitcher: function() {
        jQuery('#wb5_items_tabs div').unbind('click'); // unbind click from these elements to avoid multiplication of events
        jQuery('#wb5_items_tabs div').click(function() {
            var sheet_id = jQuery(this).attr('data-sheet-id');
            window.wb5_teacher.switchTab(sheet_id);
        });
    },
    switchTab: function(sheet_id) {
        // inactivate all tabs
        jQuery('.active_wp5_tab').removeClass('active_wp5_tab');
        // set active the clicked tab
        jQuery('#file_name_'+sheet_id).parent().addClass('active_wp5_tab');
        // hidding tab contents
        jQuery('.wb5_board_item').hide();
        // showing contend of selected tab
        jQuery('#board_item_'+sheet_id).show();
    },
    setTabBindings: function(data, unique_id, user_hash, test_hash) {
        // setting zone id, i.e board id, so we could know where are we working
        data = data.replace(/%zone_id%/g, unique_id);
        data = data.replace(/%user_hash%/g, user_hash);
        data = data.replace(/%test_hash%/g, test_hash);
        jQuery('.wb5_board_subfiles').append('<div id="board_item_'+unique_id+'" class="wb5_board_item">'+data+'</div>');
        jQuery('.wb5_board_item').hide(); // hide all wb3 boards
        jQuery('#board_item_'+unique_id).show(); // showing created board
        this.bindDeleteTabEvent();
        this.bindTabSwitcher();
        this.switchTab(unique_id);
        this.bindTestProgressWatcher();
    },
    bindTestProgressWatcher: function() {
        jQuery('.request_test_progress').unbind('click');
        jQuery('.request_test_progress').click(function() {
            var user_hash = jQuery(this).attr('data-user-hash')
            var test_hash = jQuery(this).attr('data-test-hash')
            window.socket_object.emit('request_test_progress', { user_hash: user_hash, test_hash: test_hash });
        });
    },
    getStudentTests: function(data) {
        if(this.student_tests[data.student_hash] !== undefined) {
            var send_obj = {};
            var test_name = '';
            var test_hash = '';
            for (var i = 0; i < this.student_tests[data.student_hash].length; i++) {
                test_hash = this.student_tests[data.student_hash][i];
                send_obj[test_hash] = jQuery('#prepared_tests option[value='+test_hash+']').text();
            }
            window.socket_object.emit('refresh_student_test', {
                tests: send_obj,
                student_hash: data.student_hash
            });
        }
    },
    processTestProgress: function(data, caller) {
        var student_hash = data.student_hash;
        var element;
        var test_hash = data.test_data.test_hash;
        for (var i = 0; i < jQuery('.test_'+student_hash+'_'+test_hash+' input, .test_'+student_hash+'_'+test_hash+' textarea').length; i++) {
            element = jQuery('.test_'+student_hash+'_'+test_hash+' input, .test_'+student_hash+'_'+test_hash+' textarea')[i];
            if(jQuery(element).prop('tagName') == 'INPUT') {
                switch(jQuery(element).attr('type')) {
                    case 'radio':
                        element.checked = data.test_data.test_progress[i].is_checked;
                        break;
                    case 'text':
                        jQuery(element).val(data.test_data.test_progress[i].value);
                        break;
                    case 'checkbox':
                        element.checked = data.test_data.test_progress[i].is_checked;
                        break;
                }
            } else if(jQuery(element).prop('tagName') == 'TEXTAREA') {
                jQuery(element).val(data.test_data.test_progress[i].value);
            }
        }
    },
    setStudentTestStatus: function(data, caller) {
        if(data.test_status.test_status == 'finished') {
            jQuery('#file_name_'+data.student_hash+'_'+data.test_status.test_hash).parent().addClass('test_finished');
            window.socket_object.emit('request_test_progress', { user_hash: data.student_hash, test_hash: data.test_status.test_hash });
        } else {
            jQuery('#file_name_'+data.student_hash+'_'+data.test_status.test_hash).parent().removeClass('test_finished');
        }
    },
    renameTab: function(zone_id, tab_name) {
        jQuery('#file_name_'+zone_id).html(tab_name); // setting tab filename
    //        window.board_manager.bindTreeviewer();
    },
    // indicates the active teacher tab
    teacherTabIndicator: function(data) {
        jQuery('div#wb5_items_tabs > .teacher_active_tab').removeClass('teacher_active_tab');
        jQuery('#file_name_'+data.sheet_id).parent().addClass('teacher_active_tab');
        if(data.zone_id !== undefined) {
            this.switchTab(data.zone_id);
        }
    },
    setDataFromFrindHistory: function(zone_id) {
        if(this.history_from_friend[zone_id] === null) {
            return;
        }
        if(this.history_from_friend[zone_id] !== undefined) {
            this.is_changed_from_socket = true;
            this.editors_list[zone_id].setValue(this.history_from_friend[zone_id]);
            this.is_changed_from_socket = false;
        }
    },
    // gets current board content for helping other
    getAllContents: function() {
        var contents_object = new Object();
        var final_result = new Array();
        var cnt_tabs = this.current_tabs.length;
        var unique_id = '';
        for (var i = 0; i < cnt_tabs; i++) {
            unique_id = this.current_tabs[i].toString();
            contents_object = new Object();
            contents_object.unique_id = unique_id;
            contents_object.test_status = jQuery('#board_item_'+unique_id+' .set_test_finished:checked').val();
            final_result.push(contents_object);
        }
        
        return final_result;
    },
    createBoardFromHistory: function(data) {
        // store editor data
        for (var i = 0; i < data.length; i++) {
            this.history_from_friend[data[i].unique_id] = data[i].editor_content;
        }
        
        // create tabs, editors
        for (var i = 0; i < data.length; i++) {
            this.createTab(data[i].unique_id, data[i].tab_name, 'history', data.file_name);
            if(data[i].editor_content !== null) {
            }
        }
        
    },
    applyRedrawBoard: function(data) {
        console.log('Boards loaded from HISTORY');
        this.refresh_history = data;
        var cnt = data.length;
        var main_data = new Array();
        var contains_editors = false; // flag
        for (var i = 0; i < cnt; i++) {
            main_data = JSON.parse(data[i].main_data);
            switch(data[i].act_name) {
                //                case 'board_create':
                //                    window.board_manager.addBoard(main_data.board_type, main_data.board_name, 'history');
                //                    break;
                //                case 'wb3_board_delete':
                //                    window.board_manager.deleteBoard(main_data.board_type, 'history');
                //                    break;
                case 'wb3_tab_create':
                    window.wb5.createTab(data[i].obj_id, main_data.tab_name, 'history', main_data.file_name);
                    break;
                case 'wb3_tab_delete':
                    window.wb5.deleteTab(data[i].obj_id, 'history');
                    break;
                case 'wb3_set_language':
                    contains_editors = true;
                    break;
                case 'wb3_file_name_change':
                    this.renameTab(data[i].obj_id, main_data.file_name);
                    break;
            }
        }
        if(!contains_editors) {
            delete this.refresh_history; // free memory
            window.board_manager.is_refresh = false; // set refresh status to false
        }
    },
    closeBoard: function() {
        while(this.current_tabs.length) {
            this.deleteTab(this.current_tabs[0], 'redraw');
        }
        this.deleted_tabs = [];
    }
}
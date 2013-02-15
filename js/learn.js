function requestedCoursesVote(course_id)
{
    $.getJSON(requested_course_vote_url, {
        course_id: course_id
    }, function(result) {
        if(result.status == 0)//is OK
        {
            $('.votes-'+result.id).text(result.votes);
        }
        else if (result.status = 1) //redirect to login
        {
            document.location.href = result.link;
        }
        else
        {
            //do nothing
        }
    });
}

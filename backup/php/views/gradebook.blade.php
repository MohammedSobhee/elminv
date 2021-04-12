@extends('layouts.sidebar-right')
@section('title', 'Gradebook')
@include('includes.notify')
@section('content')

<h4>Team Projects</h4>
<ul class="list-parent">
    <li><a class="show-list list2-1">Sid</a> {{-- Class --}}
        <ul class="list-student">
            <li class="list-config">
                <a href="groups?cID=168" class="loadbox-smlong">Groups</a>
                <a href="#" class="open-pending">Pending Grade</a>
                <a href="#" class="open-graded">Graded</a>
            </li>
            <li><a class="toggle-list">group 1 - marc andrey choker</a>
                <ul class="list-project">
                    <li><a class="toggle-list">mcjesus #1</a>
                        <ul class="list-project-worksheet">
                            <li><a href="grades?wID=3&group=136&project_id=1259"><span class="graded">Step 2: Pre-Development Internet Search</span> <span class="grade-complete">Graded: 5</span></a></li>
                            <li><a href="grades?wID=25&group=136&project_id=1259">
                                <span class="graded">Step 1: Confidentiality Agreement and Idea Recorder</span>
                                <span class="grade-complete">Graded: 9</span></a>
                            </li>
                        </ul>
                    </li>
                    <li><a class="toggle-list">another project</a>
                        <ul class="list-project-worksheet">
                            <li><a href="grades?wID=25&group=136&project_id=1276"><span class="graded">Step 1: Confidentiality Agreement and Idea Recorder</span> <span class="grade-complete">Graded: 1</span></a></li>
                        </ul>
                    </li>
                </ul>
            </li>
            <li><a class="toggle-list">group 2 - evgeny kuznetsov, Mahmoud Arafat, Patrik Laine, Dustin Byfuglien, Jake Guentzel</a>
                <ul class="list-project">
                    <li><a class="toggle-list">test</a>
                        <ul class="list-project-worksheet">
                            <li>This group hasn't submitted anything in the system yet.</li>
                        </ul>
                    </li>
                    <li><a class="toggle-list">Card Holder</a>
                        <ul class="list-project-worksheet">
                            <li>This group hasn't submitted anything in the system yet.</li>
                        </ul>
                    </li>

                    <li><a class="toggle-list">Coffee cups</a>
                        <ul class="list-project-worksheet">
                            <li>This group hasn't submitted anything in the system yet.</li>
                        </ul>
                    </li>
                    <li><a class="toggle-list">mcjesus #1</a>
                        <ul class="list-project-worksheet">
                            <li><a href="grades?wID=3&group=137&project_id=1259"><span class="graded">Step 2: Pre-Development Internet Search</span> <span class="grade-complete">Graded: 5</span></a></li>

                            <li><a href="grades?wID=25&group=137&project_id=1259"><span class="graded">Step 1: Confidentiality Agreement and Idea Recorder</span> <span class="grade-complete">Graded: 9</span></a></li>
                        </ul>
                    </li>
                </ul>
            </li>
        </ul>
    </li>
    <li><a class="show-list list2-2">Class 2</a> {{-- Class --}}
        <ul class="list-student">
            <li class="list-config">
                <a href="groups?cID=168" class="loadbox-smlong">Groups</a>
                <a href="#" class="open-pending">Pending Grade</a>
                <a href="#" class="open-graded">Graded</a>
            </li>
            <li><a class="toggle-list">group 1 - marc andrey choker</a>
                <ul class="list-project">
                    <li><a class="toggle-list">mcjesus #1</a>
                        <ul class="list-project-worksheet">
                            <li><a href="grades?wID=3&group=136&project_id=1259"><span class="graded">Step 2: Pre-Development Internet Search</span> <span class="grade-complete">Graded: 5</span></a></li>
                            <li><a href="grades?wID=25&group=136&project_id=1259">
                                <span class="graded">Step 1: Confidentiality Agreement and Idea Recorder</span>
                                <span class="grade-complete">Graded: 9</span></a>
                            </li>
                        </ul>
                    </li>
                    <li><a class="toggle-list">another project</a>
                        <ul class="list-project-worksheet">
                            <li><a href="grades?wID=25&group=136&project_id=1276"><span class="graded">Step 1: Confidentiality Agreement and Idea Recorder</span> <span class="grade-complete">Graded: 1</span></a></li>
                        </ul>
                    </li>
                </ul>
            </li>
            <li><a class="toggle-list">group 2 - evgeny kuznetsov, Mahmoud Arafat, Patrik Laine, Dustin Byfuglien, Jake Guentzel</a>
                <ul class="list-project">
                    <li><a class="toggle-list">test</a>
                        <ul class="list-project-worksheet">
                            <li>This group hasn't submitted anything in the system yet.</li>
                        </ul>
                    </li>
                    <li><a class="toggle-list">Card Holder</a>
                        <ul class="list-project-worksheet">
                            <li>This group hasn't submitted anything in the system yet.</li>
                        </ul>
                    </li>

                    <li><a class="toggle-list">Coffee cups</a>
                        <ul class="list-project-worksheet">
                            <li>This group hasn't submitted anything in the system yet.</li>
                        </ul>
                    </li>
                    <li><a class="toggle-list">mcjesus #1</a>
                        <ul class="list-project-worksheet">
                            <li><a href="grades?wID=3&group=137&project_id=1259"><span class="graded">Step 2: Pre-Development Internet Search</span> <span class="grade-complete">Graded: 5</span></a></li>

                            <li><a href="grades?wID=25&group=137&project_id=1259"><span class="graded">Step 1: Confidentiality Agreement and Idea Recorder</span> <span class="grade-complete">Graded: 9</span></a></li>
                        </ul>
                    </li>
                </ul>
            </li>
        </ul>
    </li>
</ul>

<br><br>
<h4>Student Projects</h4>
<ul class="list-parent">
    <li><a class="show-list list-1">Sid</a>
        <ul class="list-student">
            <li class="list-config">
                <a href="groups?cID=168" class="loadbox-smlong">Groups</a>
                <a href="#" class="open-pending">Pending Grade</a>
                <a href="#" class="open-graded">Graded</a>
            </li>
            <li><a class="toggle-list">connor mcjesus</a>
                <ul class="list-project">
                    <li><a class="toggle-list">mc jesus solo mode</a>
                        <ul class="list-project-worksheet">
                            <li>This student hasn't submited anything to be graded.</li>
                        </ul>
                    </li>
                </ul>
            </li>
            <li><a class="toggle-list">Mahmoud Arafat</a>
                <ul class="list-project">
                    <li><a class="toggle-list">Elder Watch</a>
                        <ul class="list-project-worksheet">
                            <li>This student hasn't submited anything to be graded.</li>
                        </ul>
                    </li>
                </ul>
            </li>
            <li><a class="toggle-list">Evg Malkin</a>
                <ul class="list-project">
                    <li><a class="toggle-list">test3</a>
                        <ul class="list-project-worksheet">
                            <li><a href="grades?wID=9&student=134&project_id=116"><span class="pending">Step 6: Engineering</span> <span class="grade">Grade</span></a></li>
                            <li><a href="grades?wID=10&student=134&project_id=116"><span class="pending">Step 7: Package Design</span> <span class="grade">Grade</span></a></li>
                            <li><a href="grades?wID=11&student=134&project_id=116"><span class="pending">Step 8: Graphic Design</span> <span class="grade">Grade</span></a></li>
                            <li><a href="grades?wID=24&student=134&project_id=116"><span class="pending">Step 2: Pre-Development Store Search</span> <span class="grade">Grade</span></a></li>
                            <li><a href="grades?wID=25&student=134&project_id=116"><span class="graded">Step 1: Confidentiality Agreement and Idea Recorder</span> <span class="grade-complete">Graded: 10</span></a></li>
                        </ul>
                    </li>
                </ul>
            </li>
            <li><a class="toggle-list">LED Lamp</a>
                <ul class="list-project-worksheet">
                    <li>This student hasn't submited anything to be graded.</li>
                </ul>
            </li>
            <li><a class="toggle-list">Standing Desk</a>
                <ul class="list-project-worksheet">
                    <li><a href="grades?wID=3&student=134&project_id=409"><span class="pending">Step 2: Pre-Development Internet Search</span> <span class="grade">Grade</span></a></li>
                    <li><a href="grades?wID=8&student=134&project_id=409"><span class="pending">Step 5: Concept Model</span> <span class="grade">Grade</span></a></li>
                    <li><a href="grades?wID=10&student=134&project_id=409"><span class="pending">Step 7: Package Design</span> <span class="grade">Grade</span></a></li>
                    <li><a href="grades?wID=24&student=134&project_id=409"><span class="pending">Step 2: Pre-Development Store Search</span> <span class="grade">Grade</span></a></li>
                </ul>
            </li>
        </ul>
    </li>

    <li><a class="show-list list-2">Class 2</a>
        <ul class="list-student">
            <li class="list-config">
                <a href="groups?cID=168" class="loadbox-smlong">Groups</a>
                <a href="#" class="open-pending">Pending Grade</a>
                <a href="#" class="open-graded">Graded</a>
            </li>
            <li><a class="toggle-list">connor mcjesus</a>
                <ul class="list-project">
                    <li><a class="toggle-list">mc jesus solo mode</a>
                        <ul class="list-project-worksheet">
                            <li>This student hasn't submited anything to be graded.</li>
                        </ul>
                    </li>
                </ul>
            </li>
            <li><a class="toggle-list">Mahmoud Arafat</a>
                <ul class="list-project">
                    <li><a class="toggle-list">Elder Watch</a>
                        <ul class="list-project-worksheet">
                            <li>This student hasn't submited anything to be graded.</li>
                        </ul>
                    </li>
                </ul>
            </li>
            <li><a class="toggle-list">Evg Malkin</a>
                <ul class="list-project">
                    <li><a class="toggle-list">test3</a>
                        <ul class="list-project-worksheet">
                            <li><a href="grades?wID=9&student=134&project_id=116"><span class="pending">Step 6: Engineering</span> <span class="grade">Grade</span></a></li>
                            <li><a href="grades?wID=10&student=134&project_id=116"><span class="pending">Step 7: Package Design</span> <span class="grade">Grade</span></a></li>
                            <li><a href="grades?wID=11&student=134&project_id=116"><span class="pending">Step 8: Graphic Design</span> <span class="grade">Grade</span></a></li>
                            <li><a href="grades?wID=24&student=134&project_id=116"><span class="pending">Step 2: Pre-Development Store Search</span> <span class="grade">Grade</span></a></li>
                            <li><a href="grades?wID=25&student=134&project_id=116"><span class="graded">Step 1: Confidentiality Agreement and Idea Recorder</span> <span class="grade-complete">Graded: 10</span></a></li>
                        </ul>
                    </li>
                </ul>
            </li>
            <li><a class="toggle-list">LED Lamp</a>
                <ul class="list-project-worksheet">
                    <li>This student hasn't submited anything to be graded.</li>
                </ul>
            </li>
            <li><a class="toggle-list">Standing Desk</a>
                <ul class="list-project-worksheet">
                    <li><a href="grades?wID=3&student=134&project_id=409"><span class="pending">Step 2: Pre-Development Internet Search</span> <span class="grade">Grade</span></a></li>
                    <li><a href="grades?wID=8&student=134&project_id=409"><span class="pending">Step 5: Concept Model</span> <span class="grade">Grade</span></a></li>
                    <li><a href="grades?wID=10&student=134&project_id=409"><span class="pending">Step 7: Package Design</span> <span class="grade">Grade</span></a></li>
                    <li><a href="grades?wID=24&student=134&project_id=409"><span class="pending">Step 2: Pre-Development Store Search</span> <span class="grade">Grade</span></a></li>
                </ul>
            </li>
        </ul>
    </li>
</ul>




@endsection

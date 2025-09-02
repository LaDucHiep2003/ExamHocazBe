
<?php

include_once __DIR__ . '/../Controller/QuestionController.php';
include_once __DIR__ . '/../Routes/handleRouter.php';
include_once __DIR__ . "/../Controller/ExamController.php";
include_once __DIR__ . "/../Controller/ExamCategoryController.php";
include_once __DIR__ . "/../Controller/IRTController.php";
include_once __DIR__ . "/../Controller/ResultController.php";
include_once __DIR__ . "/../Controller/ResultDetailController.php";
include_once __DIR__ . "/../Controller/UserController.php";
include_once __DIR__ . "/../Controller/ClassRoomController.php";
include_once __DIR__ . "/../Controller/SubjectController.php";
include_once __DIR__ . "/../Controller/ChapterController.php";
include_once __DIR__ . "/../Controller/CategoryParentController.php";
include_once __DIR__ . "/../Controller/IRTController.php";

$QuestionController = new QuestionsController();
$ExamController = new ExamController();
$ExamCategoryController = new ExamCategoryController();
$IRTController = new IRTController();
$ResultController = new ResultController();
$ResultDetailController = new ResultDetailController();
$UserController = new UserController();
$ClassRoomController = new ClassRoomController();
$SubjectController = new SubjectController();
$ChapterController = new ChapterController();
$CategoryParentController = new CategoryParentController();
$IRTController = new IRTController();

$methodRequest = $_SERVER['REQUEST_METHOD'];
$UriRequest = $_SERVER['REQUEST_URI'];
// lấy URI chính
$UriRequest = strtok($UriRequest, '?');
$routers = [
    "GET" => [
        '/questions' => function () use ($QuestionController) {
            if (isset($_GET['exam'])) {
                $QuestionController->getQuestionInExam((int)$_GET['exam']);
            }else{
                $QuestionController->index();
            }
        },
        '/exams' => function () use ($ExamController) {
            if (isset($_GET['chapter'])) {
                $ExamController->getExamsOfChapter((int)$_GET['chapter']);
            }else{
                $ExamController->index();
            }
        },
        '/exams/(\d+)' => function ($id) use ($ExamController) {
            $ExamController->detail($id);
        },
        '/category' => function () use ($ExamCategoryController) {
            $ExamCategoryController->index();
        },
        '/category/(\d+)' => function ($id) use ($ExamCategoryController) {
            $ExamCategoryController->detail($id);
        },
        '/categoryparent/([\w-]+)' => function ($slug) use ($CategoryParentController) {
            $CategoryParentController->detail($slug);
        },
        '/categoryparent' => function () use ($CategoryParentController) {
            if (isset($_GET['id'])) {
                $CategoryParentController->detailById((int)$_GET['id']);
            }else{
                $CategoryParentController->index();
            }
        },
        '/subject/(\d+)' => function ($id) use ($SubjectController) {
            $SubjectController->detail($id);
        },
        '/chapter/(\d+)' => function ($id) use ($ChapterController) {
            $ChapterController->detail($id);
        },
        '/questions/(\d+)' => function ($id) use ($QuestionController) {
            $QuestionController->detail($id);
        },
        '/results/(\d+)' => function ($id) use ($ResultController) {
            $ResultController->detail($id);
        },
        '/results' => function () use ($ResultController) {
            if (isset($_GET['user'])) {
                $ResultController->getResultOfUser((int)$_GET['user']);
            }else{
                $ResultController->index();
            }
        },
        '/results/questions/(\d+)' => function ($id) use ($ResultController) {
            $ResultController->getQuestions($id);
        },
        '/resultDetail/detail/(\d+)' => function ($id) use ($ResultDetailController) {
            $ResultDetailController->detail($id);
        },
        "/users" => function () use ($UserController) {
            $UserController->index();
        },
        '/users/(\d+)' => function ($id) use ($UserController) {
            $UserController->detail($id);
        },
        "/users/token" => function () use ($UserController) {
            $UserController->getUserFromToken();
        },
        '/subjects' => function () use ($SubjectController) {
            if (isset($_GET['category'])) {
                $SubjectController->getSubjectsOfCategory((int)$_GET['category']);
            }else{
                $SubjectController->index();
            }
        },
        '/chapters' => function () use ($ChapterController) {
            if (isset($_GET['subject'])) {
                $ChapterController->getChaptersOfSubject((int)$_GET['subject']);
            }else{
                $ChapterController->index();
            }
        },
        "/irt/student" => function () use ($IRTController) {
            $IRTController->getInformationStudent();
        },
        "/irt/question" => function () use ($IRTController) {
            $IRTController->getInformationQuestion();
        },
    ],
    "POST" => [
        "/category" => function () use ($ExamCategoryController) {
            $ExamCategoryController->create();
        },
        "/categoryparent" => function () use ($CategoryParentController) {
            $CategoryParentController->create();
        },
        "/subject" => function () use ($SubjectController) {
            $SubjectController->create();
        },
        "/chapter" => function () use ($ChapterController) {
            $ChapterController->create();
        },
        "/exams" => function () use ($ExamController) {
            $ExamController->create();
        },
        "/questions" => function () use ($QuestionController) {
            $QuestionController->create();
        },
        "/difficulty" => function () use ($IRTController) {
            $IRTController->difficulty();
        },
        "/theta" => function () use ($IRTController) {
            $IRTController->theta();
        },
        "/outfit" => function () use ($IRTController) {
            $IRTController->Outfit();
        },
        "/results" => function () use ($ResultController) {
            $ResultController->create();
        },
        "/login" => function () use ($UserController) {
            $UserController->login();
        },
        "/register" => function () use ($UserController) {
            $UserController->register();
        },
    ],

    "PATCH" => [
        '/exams' => function () use ($ExamController) {
            $ExamController->edit();
        },
        '/category/(\d+)' => function ($id) use ($ExamCategoryController) {
            $ExamCategoryController->edit($id);
        },
        '/categoryparent/(\d+)' => function ($id) use ($CategoryParentController) {
            $CategoryParentController->edit($id);
        },
        '/questions/(\d+)' => function ($id) use ($QuestionController) {
            $QuestionController->edit($id);
        },
        '/subject/(\d+)' => function ($id) use ($SubjectController) {
            $SubjectController->edit($id);
        },
        '/chapter/(\d+)' => function ($id) use ($ChapterController) {
            $ChapterController->edit($id);
        },
        '/users/(\d+)' => function ($id) use ($UserController) {
            $UserController->edit($id);
        },
    ],

    "DELETE" =>[
        "/category/(\d+)" => function ($id) use ($ExamCategoryController) {
            $ExamCategoryController->delete($id);
        },
        "/categoryparent/(\d+)" => function ($id) use ($CategoryParentController) {
            $CategoryParentController->delete($id);
        },
        "/exams/(\d+)" => function ($id) use ($ExamController) {
            $ExamController->delete($id);
        },
        "/questions/(\d+)" => function ($id) use ($QuestionController) {
            $QuestionController->delete($id);
        },
        "/subject/(\d+)" => function ($id) use ($SubjectController) {
            $SubjectController->delete($id);
        },
        "/chapter/(\d+)" => function ($id) use ($ChapterController) {
            $ChapterController->delete($id);
        },
        "/accounts/(\d+)" => function ($id) use ($UserController) {
            $UserController->delete($id);
        }
    ],
    'OPTIONS' => function () {
        http_response_code(204); // No Content
        exit();
    }

];

// gọi hàm route để định tuyến request đến các controller
HandleRoute::handleroute($routers, $methodRequest, $UriRequest);

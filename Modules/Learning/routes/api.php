<?php

use Illuminate\Support\Facades\Route;
use Modules\Learning\Http\Controllers\AssignmentController;
use Modules\Learning\Http\Controllers\QuizController;
use Modules\Learning\Http\Controllers\QuizSubmissionController;
use Modules\Learning\Http\Controllers\SubmissionController;

Route::middleware(['auth:api'])->prefix('v1')->scopeBindings()->group(function () {
    Route::get('courses/{course:slug}/assignments', [AssignmentController::class, 'index'])
        ->middleware('can:viewAssignments,course')
        ->name('courses.assignments.index');

    Route::get('courses/{course:slug}/assessments', [\Modules\Learning\Http\Controllers\AssessmentController::class, 'index'])
        ->middleware(['role:Superadmin|Admin|Instructor'])
        ->name('courses.assessments.index');

    Route::get('courses/{course:slug}/assignments/incomplete', [AssignmentController::class, 'indexIncomplete'])
        ->middleware('can:viewAssignments,course')
        ->name('courses.assignments.incomplete');

    Route::get('assignments/{assignment}', [AssignmentController::class, 'show'])
        ->middleware('can:view,assignment')
        ->name('assignments.show');

    Route::get('assignments/{assignment}/prerequisites/check', [AssignmentController::class, 'checkPrerequisites'])
        ->middleware('can:view,assignment')
        ->name('assignments.prerequisites.check');

    Route::get('assignments/{assignment}/submissions', [SubmissionController::class, 'index'])
        ->middleware('can:view,assignment')
        ->name('assignments.submissions.index');

    Route::get('assignments/{assignment}/submissions/highest', [SubmissionController::class, 'highestSubmission'])
        ->middleware('can:view,assignment')
        ->name('assignments.submissions.highest');

    Route::get('assignments/{assignment}/submissions/{submission}', [SubmissionController::class, 'showForAssignment'])
        ->name('assignments.submissions.detail');

    
    Route::middleware(['role:Superadmin|Admin|Instructor'])->group(function () {
        Route::post('assignments', [AssignmentController::class, 'store'])
            ->name('assignments.store');

        Route::put('assignments/{assignment}', [AssignmentController::class, 'update'])
            ->middleware('can:update,assignment')
            ->name('assignments.update');

        Route::delete('assignments/{assignment}', [AssignmentController::class, 'destroy'])
            ->middleware('can:delete,assignment')
            ->name('assignments.destroy');

        Route::put('assignments/{assignment}/publish', [AssignmentController::class, 'publish'])
            ->middleware('can:update,assignment')
            ->name('assignments.publish');

        Route::put('assignments/{assignment}/unpublish', [AssignmentController::class, 'unpublish'])
            ->middleware('can:update,assignment')
            ->name('assignments.unpublish');

        Route::put('assignments/{assignment}/archived', [AssignmentController::class, 'archive'])
            ->middleware('can:update,assignment')
            ->name('assignments.archive');

        Route::post('assignments/{assignment}/duplicate', [AssignmentController::class, 'duplicate'])
            ->middleware('can:duplicate,assignment')
            ->name('assignments.duplicate');

        Route::get('submissions/search', [SubmissionController::class, 'search'])
            ->name('submissions.search');
    });

    
    Route::post('assignments/{assignment}/submissions', [SubmissionController::class, 'store'])
        ->middleware('xp.info')
        ->name('assignments.submissions.store');

    Route::put('submissions/{submission}', [SubmissionController::class, 'update'])
        ->middleware('can:update,submission')
        ->name('submissions.update');

    Route::post('submissions/{submission}/submit', [SubmissionController::class, 'submit'])
        ->middleware(['can:submit,submission', 'xp.info'])
        ->name('submissions.submit');

    

    

    Route::get('courses/{course:slug}/quizzes', [QuizController::class, 'index'])
        ->middleware('can:viewAny,'.Modules\Learning\Models\Quiz::class)
        ->name('courses.quizzes.index');

    Route::get('quizzes/{quiz}', [QuizController::class, 'show'])
        ->middleware('can:view,quiz')
        ->name('quizzes.show');

    Route::get('quizzes/{quiz}/questions', [QuizController::class, 'listQuestions'])
        ->middleware('can:view,quiz')
        ->name('quizzes.questions.index');

    Route::get('quizzes/{quiz}/submissions', [QuizSubmissionController::class, 'index'])
        ->middleware('can:view,quiz')
        ->name('quizzes.submissions.index');

    Route::get('quizzes/{quiz}/submissions/highest', [QuizSubmissionController::class, 'highestSubmission'])
        ->middleware('can:view,quiz')
        ->name('quizzes.submissions.highest');

    Route::post('quizzes/{quiz}/submissions/start', [QuizSubmissionController::class, 'start'])
        ->middleware(['can:takeQuiz,quiz', 'xp.info'])
        ->name('quizzes.submissions.start');

    Route::get('quiz-submissions/{submission}/questions', [QuizSubmissionController::class, 'listQuestions'])
        ->middleware('can:view,submission')
        ->name('quiz-submissions.questions.index');


    Route::get('quiz-submissions/{submission}/overview', [QuizSubmissionController::class, 'overview'])
        ->middleware('can:view,submission')
        ->name('quiz-submissions.overview');

    Route::post('quiz-submissions/{submission}/answers', [QuizSubmissionController::class, 'saveAnswer'])
        ->middleware('can:update,submission')
        ->name('quiz-submissions.answers.store');

    Route::post('quiz-submissions/{submission}/submit', [QuizSubmissionController::class, 'submit'])
        ->middleware(['can:update,submission', 'xp.info'])
        ->name('quiz-submissions.submit');

    Route::post('quiz-submissions/{submission}/takeover', [QuizSubmissionController::class, 'takeover'])
        ->middleware('can:takeover,submission')
        ->name('quiz-submissions.takeover');

    Route::get('quiz-submissions/{submission}', [QuizSubmissionController::class, 'show'])
        ->middleware('can:view,submission')
        ->name('quiz-submissions.show');

    Route::middleware(['role:Superadmin|Admin|Instructor'])->group(function () {
        Route::post('quizzes', [QuizController::class, 'store'])
            ->name('quizzes.store');

        Route::put('quizzes/{quiz}', [QuizController::class, 'update'])
            ->middleware('can:update,quiz')
            ->name('quizzes.update');

        Route::delete('quizzes/{quiz}', [QuizController::class, 'destroy'])
            ->middleware('can:delete,quiz')
            ->name('quizzes.destroy');

        Route::put('quizzes/{quiz}/publish', [QuizController::class, 'publish'])
            ->middleware('can:update,quiz')
            ->name('quizzes.publish');

        Route::put('quizzes/{quiz}/unpublish', [QuizController::class, 'unpublish'])
            ->middleware('can:update,quiz')
            ->name('quizzes.unpublish');

        Route::put('quizzes/{quiz}/archived', [QuizController::class, 'archive'])
            ->middleware('can:update,quiz')
            ->name('quizzes.archive');

        Route::post('quizzes/{quiz}/questions', [QuizController::class, 'addQuestion'])
            ->middleware('can:update,quiz')
            ->name('quizzes.questions.store');

        Route::post('quizzes/{quiz}/questions/reorder', [QuizController::class, 'reorderQuestions'])
            ->middleware('can:update,quiz')
            ->name('quizzes.questions.reorder');

        Route::put('quizzes/{quiz}/questions/reorder', [QuizController::class, 'reorderQuestions'])
            ->middleware('can:update,quiz')
            ->name('quizzes.questions.reorder.put');

        Route::get('quizzes/{quiz}/questions/{question}', [QuizController::class, 'showQuestion'])
            ->middleware('can:view,quiz')
            ->name('quizzes.questions.show');

        Route::put('quizzes/{quiz}/questions/{question}', [QuizController::class, 'updateQuestion'])
            ->middleware('can:update,quiz')
            ->name('quizzes.questions.update');

        Route::delete('quizzes/{quiz}/questions/{question}', [QuizController::class, 'deleteQuestion'])
            ->middleware('can:update,quiz')
            ->name('quizzes.questions.destroy');
    });
});

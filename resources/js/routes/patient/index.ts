import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
/**
* @see \App\Http\Controllers\PatientStoryController::story
 * @see app/Http/Controllers/PatientStoryController.php:19
 * @route '/patient-story'
 */
export const story = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: story.url(options),
    method: 'get',
})

story.definition = {
    methods: ["get","head"],
    url: '/patient-story',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\PatientStoryController::story
 * @see app/Http/Controllers/PatientStoryController.php:19
 * @route '/patient-story'
 */
story.url = (options?: RouteQueryOptions) => {
    return story.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PatientStoryController::story
 * @see app/Http/Controllers/PatientStoryController.php:19
 * @route '/patient-story'
 */
story.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: story.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\PatientStoryController::story
 * @see app/Http/Controllers/PatientStoryController.php:19
 * @route '/patient-story'
 */
story.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: story.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\PatientStoryController::story
 * @see app/Http/Controllers/PatientStoryController.php:19
 * @route '/patient-story'
 */
    const storyForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: story.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\PatientStoryController::story
 * @see app/Http/Controllers/PatientStoryController.php:19
 * @route '/patient-story'
 */
        storyForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: story.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\PatientStoryController::story
 * @see app/Http/Controllers/PatientStoryController.php:19
 * @route '/patient-story'
 */
        storyForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: story.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    story.form = storyForm
/**
* @see \App\Http\Controllers\PatientDashboardController::dashboard
 * @see app/Http/Controllers/PatientDashboardController.php:17
 * @route '/patient/dashboard'
 */
export const dashboard = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})

dashboard.definition = {
    methods: ["get","head"],
    url: '/patient/dashboard',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\PatientDashboardController::dashboard
 * @see app/Http/Controllers/PatientDashboardController.php:17
 * @route '/patient/dashboard'
 */
dashboard.url = (options?: RouteQueryOptions) => {
    return dashboard.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PatientDashboardController::dashboard
 * @see app/Http/Controllers/PatientDashboardController.php:17
 * @route '/patient/dashboard'
 */
dashboard.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\PatientDashboardController::dashboard
 * @see app/Http/Controllers/PatientDashboardController.php:17
 * @route '/patient/dashboard'
 */
dashboard.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: dashboard.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\PatientDashboardController::dashboard
 * @see app/Http/Controllers/PatientDashboardController.php:17
 * @route '/patient/dashboard'
 */
    const dashboardForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: dashboard.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\PatientDashboardController::dashboard
 * @see app/Http/Controllers/PatientDashboardController.php:17
 * @route '/patient/dashboard'
 */
        dashboardForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: dashboard.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\PatientDashboardController::dashboard
 * @see app/Http/Controllers/PatientDashboardController.php:17
 * @route '/patient/dashboard'
 */
        dashboardForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: dashboard.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    dashboard.form = dashboardForm
const patient = {
    story: Object.assign(story, story),
dashboard: Object.assign(dashboard, dashboard),
}

export default patient
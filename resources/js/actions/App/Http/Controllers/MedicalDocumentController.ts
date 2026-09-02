import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\MedicalDocumentController::printResume
 * @see app/Http/Controllers/MedicalDocumentController.php:18
 * @route '/staff/print/medical-resume/{id}'
 */
export const printResume = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: printResume.url(args, options),
    method: 'get',
})

printResume.definition = {
    methods: ["get","head"],
    url: '/staff/print/medical-resume/{id}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\MedicalDocumentController::printResume
 * @see app/Http/Controllers/MedicalDocumentController.php:18
 * @route '/staff/print/medical-resume/{id}'
 */
printResume.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return printResume.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\MedicalDocumentController::printResume
 * @see app/Http/Controllers/MedicalDocumentController.php:18
 * @route '/staff/print/medical-resume/{id}'
 */
printResume.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: printResume.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\MedicalDocumentController::printResume
 * @see app/Http/Controllers/MedicalDocumentController.php:18
 * @route '/staff/print/medical-resume/{id}'
 */
printResume.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: printResume.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\MedicalDocumentController::printResume
 * @see app/Http/Controllers/MedicalDocumentController.php:18
 * @route '/staff/print/medical-resume/{id}'
 */
    const printResumeForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: printResume.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\MedicalDocumentController::printResume
 * @see app/Http/Controllers/MedicalDocumentController.php:18
 * @route '/staff/print/medical-resume/{id}'
 */
        printResumeForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: printResume.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\MedicalDocumentController::printResume
 * @see app/Http/Controllers/MedicalDocumentController.php:18
 * @route '/staff/print/medical-resume/{id}'
 */
        printResumeForm.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: printResume.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    printResume.form = printResumeForm
/**
* @see \App\Http\Controllers\MedicalDocumentController::printSickLetter
 * @see app/Http/Controllers/MedicalDocumentController.php:59
 * @route '/staff/print/sick-letter/{id}'
 */
export const printSickLetter = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: printSickLetter.url(args, options),
    method: 'get',
})

printSickLetter.definition = {
    methods: ["get","head"],
    url: '/staff/print/sick-letter/{id}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\MedicalDocumentController::printSickLetter
 * @see app/Http/Controllers/MedicalDocumentController.php:59
 * @route '/staff/print/sick-letter/{id}'
 */
printSickLetter.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return printSickLetter.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\MedicalDocumentController::printSickLetter
 * @see app/Http/Controllers/MedicalDocumentController.php:59
 * @route '/staff/print/sick-letter/{id}'
 */
printSickLetter.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: printSickLetter.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\MedicalDocumentController::printSickLetter
 * @see app/Http/Controllers/MedicalDocumentController.php:59
 * @route '/staff/print/sick-letter/{id}'
 */
printSickLetter.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: printSickLetter.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\MedicalDocumentController::printSickLetter
 * @see app/Http/Controllers/MedicalDocumentController.php:59
 * @route '/staff/print/sick-letter/{id}'
 */
    const printSickLetterForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: printSickLetter.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\MedicalDocumentController::printSickLetter
 * @see app/Http/Controllers/MedicalDocumentController.php:59
 * @route '/staff/print/sick-letter/{id}'
 */
        printSickLetterForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: printSickLetter.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\MedicalDocumentController::printSickLetter
 * @see app/Http/Controllers/MedicalDocumentController.php:59
 * @route '/staff/print/sick-letter/{id}'
 */
        printSickLetterForm.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: printSickLetter.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    printSickLetter.form = printSickLetterForm
/**
* @see \App\Http\Controllers\MedicalDocumentController::printReferral
 * @see app/Http/Controllers/MedicalDocumentController.php:93
 * @route '/staff/print/referral-letter/{id}'
 */
export const printReferral = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: printReferral.url(args, options),
    method: 'get',
})

printReferral.definition = {
    methods: ["get","head"],
    url: '/staff/print/referral-letter/{id}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\MedicalDocumentController::printReferral
 * @see app/Http/Controllers/MedicalDocumentController.php:93
 * @route '/staff/print/referral-letter/{id}'
 */
printReferral.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return printReferral.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\MedicalDocumentController::printReferral
 * @see app/Http/Controllers/MedicalDocumentController.php:93
 * @route '/staff/print/referral-letter/{id}'
 */
printReferral.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: printReferral.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\MedicalDocumentController::printReferral
 * @see app/Http/Controllers/MedicalDocumentController.php:93
 * @route '/staff/print/referral-letter/{id}'
 */
printReferral.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: printReferral.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\MedicalDocumentController::printReferral
 * @see app/Http/Controllers/MedicalDocumentController.php:93
 * @route '/staff/print/referral-letter/{id}'
 */
    const printReferralForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: printReferral.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\MedicalDocumentController::printReferral
 * @see app/Http/Controllers/MedicalDocumentController.php:93
 * @route '/staff/print/referral-letter/{id}'
 */
        printReferralForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: printReferral.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\MedicalDocumentController::printReferral
 * @see app/Http/Controllers/MedicalDocumentController.php:93
 * @route '/staff/print/referral-letter/{id}'
 */
        printReferralForm.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: printReferral.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    printReferral.form = printReferralForm
/**
* @see \App\Http\Controllers\MedicalDocumentController::printReceipt
 * @see app/Http/Controllers/MedicalDocumentController.php:125
 * @route '/staff/billing/{id}/print-receipt'
 */
export const printReceipt = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: printReceipt.url(args, options),
    method: 'get',
})

printReceipt.definition = {
    methods: ["get","head"],
    url: '/staff/billing/{id}/print-receipt',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\MedicalDocumentController::printReceipt
 * @see app/Http/Controllers/MedicalDocumentController.php:125
 * @route '/staff/billing/{id}/print-receipt'
 */
printReceipt.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return printReceipt.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\MedicalDocumentController::printReceipt
 * @see app/Http/Controllers/MedicalDocumentController.php:125
 * @route '/staff/billing/{id}/print-receipt'
 */
printReceipt.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: printReceipt.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\MedicalDocumentController::printReceipt
 * @see app/Http/Controllers/MedicalDocumentController.php:125
 * @route '/staff/billing/{id}/print-receipt'
 */
printReceipt.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: printReceipt.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\MedicalDocumentController::printReceipt
 * @see app/Http/Controllers/MedicalDocumentController.php:125
 * @route '/staff/billing/{id}/print-receipt'
 */
    const printReceiptForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: printReceipt.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\MedicalDocumentController::printReceipt
 * @see app/Http/Controllers/MedicalDocumentController.php:125
 * @route '/staff/billing/{id}/print-receipt'
 */
        printReceiptForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: printReceipt.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\MedicalDocumentController::printReceipt
 * @see app/Http/Controllers/MedicalDocumentController.php:125
 * @route '/staff/billing/{id}/print-receipt'
 */
        printReceiptForm.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: printReceipt.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    printReceipt.form = printReceiptForm
const MedicalDocumentController = { printResume, printSickLetter, printReferral, printReceipt }

export default MedicalDocumentController
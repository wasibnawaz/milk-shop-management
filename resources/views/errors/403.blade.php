<x-errors.layout code="403" title="Not Allowed"
    :message="$exception?->getMessage() ?: 'Your role does not have permission to view this page.'" />

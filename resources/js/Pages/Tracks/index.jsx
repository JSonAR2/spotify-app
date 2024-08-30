import TracksTable from '@/Components/TracksTable';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

export default function TracksIndex({ auth, tracks }) {
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Tracks</h2>}
        >
            <Head title="Tracks" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <TracksTable tracks={tracks} />
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

import PlaylistsTable from "@/Components/PlaylistsTable";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head } from "@inertiajs/react";

export default function PlaylistsIndex({ auth, playlists }) {
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <h2 className="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Playlists
                </h2>
            }
        >
            <Head title="Playlists" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <PlaylistsTable playlists={playlists} />
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

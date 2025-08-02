import GenresTable from "@/Components/GenresTable";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head } from "@inertiajs/react";

export default function GenresIndex({ auth, genres }) {
    console.log("Genres data:", genres);
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <h2 className="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Genres
                </h2>
            }
        >
            <Head title="Genres" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <GenresTable genres={genres} />
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

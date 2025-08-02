import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head } from "@inertiajs/react";

export default function Edit({ auth, user, tracks }) {
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <h2 className="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Profile - {user.name}
                </h2>
            }
        >
            <Head title="Profile" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    <div className="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                        {/* Display user tracks here */}
                        <div className="max-w-xl">
                            <h3 className="text-lg font-semibold mb-4">
                                User Tracks
                            </h3>
                            {tracks.length > 0 ? (
                                <ul className="list-disc pl-5">
                                    {tracks.map((track) => (
                                        <li key={track.id} className="mb-2">
                                            <strong>{track.name}</strong> by{" "}
                                            {track.artist} -{" "}
                                            {track.genres.length > 0
                                                ? track.genres.join(", ")
                                                : "No genres available"}
                                        </li>
                                    ))}
                                </ul>
                            ) : (
                                <p>No tracks available for this user.</p>
                            )}
                        </div>
                    </div>
                    <div className="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                        {/* User Playlists   */}
                        <div className="max-w-xl">
                            <h3 className="text-lg font-semibold mb-4">
                                User Playlists
                            </h3>
                            {user.playists && user.playlists.length > 0 ? (
                                <ul className="list-disc pl-5">
                                    {user.playlists.map((playlist) => (
                                        <li key={playlist.id} className="mb-2">
                                            <strong>{playlist.name}</strong> -{" "}
                                            {playlist.description ||
                                                "No description available"}
                                        </li>
                                    ))}
                                </ul>
                            ) : (
                                <p>No playlists available for this user.</p>
                            )}
                        </div>
                    </div>

                    <div className="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                        {/* User Favorites */}
                        {/* <div className="max-w-xl">
                            <h3 className="text-lg font-semibold mb-4">
                                User Favorites
                            </h3>
                            {user.favorites.length > 0 ? (
                                <ul className="list-disc pl-5">
                                    {user.favorites.map((favorite) => (
                                        <li key={favorite.id} className="mb-2">
                                            <strong>{favorite.name}</strong> -{" "}
                                            {favorite.artist} -{" "}
                                            {favorite.genres.length > 0
                                                ? favorite.genres.join(", ")
                                                : "No genres available"}
                                        </li>
                                    ))}
                                </ul>
                            ) : (
                                <p>No favorites available for this user.</p>
                            )}
                        </div> */}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

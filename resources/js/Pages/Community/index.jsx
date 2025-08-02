import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head, Link } from "@inertiajs/react";
import { Card } from "@mui/material";

export default function CommunityIndex({ auth, users }) {
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <h2 className="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Community
                </h2>
            }
        >
            <Head title="Community" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <Card sx={{ width: "100%" }}>
                            <div className="p-6 text-gray-900 dark:text-gray-100">
                                <h3 className="text-lg font-semibold mb-4">
                                    Other Users
                                </h3>
                                {users.map((user) => (
                                    <Link
                                        key={user.id}
                                        href={`/profile/${user.id}`}
                                    >
                                        <div className="mb-4">
                                            <strong>{user.name}</strong> -{" "}
                                            {user.track_count} tracks
                                            <div>
                                                <span className="font-medium">
                                                    Tracks in common:
                                                </span>{" "}
                                                {user.tracks_in_common &&
                                                user.tracks_in_common.length > 0
                                                    ? user.tracks_in_common.join(
                                                          ", "
                                                      )
                                                    : "None"}
                                            </div>
                                            <div>
                                                <span className="font-medium">
                                                    Top genres:
                                                </span>{" "}
                                                {user.top_genres &&
                                                user.top_genres.length > 0
                                                    ? user.top_genres
                                                          .slice(0, 3)
                                                          .join(", ")
                                                    : "N/A"}
                                            </div>
                                        </div>
                                    </Link>
                                ))}
                            </div>
                        </Card>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

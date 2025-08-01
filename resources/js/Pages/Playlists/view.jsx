import PlaylistsTable from "@/Components/PlaylistsTable";
import TracksTable from "@/Components/TracksTable";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head, router } from "@inertiajs/react";
import { Card, CardContent, Typography } from "@mui/material";
import { Button } from "@mui/material";

export default function PlaylistsIndex({ auth, playlist, tracks, ...props }) {
    console.log(props);
    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Playlists" />

            <div className="py-8">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <Card className="mb-4">
                        <CardContent>
                            <div className="flex justify-between">
                                <Typography variant="h5">
                                    {playlist.name}
                                </Typography>
                                <Typography variant="h6">
                                    {playlist.track_count} Tracks
                                </Typography>
                                <Button
                                    onClick={() => {
                                        if (
                                            confirm(
                                                "Are you sure you want to delete this playlist?"
                                            )
                                        ) {
                                            axios
                                                .delete(
                                                    `/playlists/delete_playlist?id=${playlist.id}`
                                                )
                                                .then((response) => {
                                                    console.log(
                                                        "Playlist deleted",
                                                        response.data
                                                    );
                                                    router.visit("/playlists");
                                                    // window.history.back();
                                                })
                                                .catch((error) => {
                                                    console.error(
                                                        "There was an error deleting the playlist!",
                                                        error
                                                    );
                                                });
                                        }
                                    }}
                                    variant="contained"
                                    color="error"
                                >
                                    Delete
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                    <div className="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <TracksTable tracks={tracks} />
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

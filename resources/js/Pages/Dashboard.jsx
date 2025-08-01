import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head } from "@inertiajs/react";
import { Card, CardContent, CardHeader } from "@mui/material";

export default function Dashboard({ auth, ...props }) {
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <h2 className="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Dashboard
                </h2>
            }
        >
            <Head title="Dashboard" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 flex justify-between">
                    <Card sx={{ width: "19%" }}>
                        <CardHeader title="Saved Tracks" />
                        <CardContent>{props.saved_tracks}</CardContent>
                    </Card>
                    <Card sx={{ width: "19%" }}>
                        <CardHeader title="Your Tracks" />
                        <CardContent>{props.your_tracks}</CardContent>
                    </Card>
                    <Card sx={{ width: "19%" }}>
                        <CardHeader title="Number of Artists" />
                        <CardContent>
                            {props.number_of_different_artists}
                        </CardContent>
                    </Card>
                    <Card sx={{ width: "19%" }}>
                        <CardHeader title="Your Albums" />
                        <CardContent>
                            {props.number_of_different_albums}
                        </CardContent>
                    </Card>
                    <Card sx={{ width: "19%" }}>
                        <CardHeader title="Your Playlists" />
                        <CardContent>{props.your_playlists}</CardContent>
                    </Card>
                </div>
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 flex justify-between mt-2">
                    <Card sx={{ width: "48%" }}>
                        <CardHeader title="Top 5 Artists" />
                        <CardContent>
                            {props.top_5_artists &&
                                props.top_5_artists.map((artist, index) => (
                                    <div key={index}>
                                        {artist.artist_name} (
                                        {artist.artist_count})
                                    </div>
                                ))}
                        </CardContent>
                    </Card>
                    <Card sx={{ width: "48%" }}>
                        <CardHeader title="Top 5 Tracks" />
                        <CardContent>
                            {props.top_5_tracks &&
                                props.top_5_tracks.map((track, index) => (
                                    <div key={index}>
                                        {track.name} - {track.artist_name}
                                    </div>
                                ))}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

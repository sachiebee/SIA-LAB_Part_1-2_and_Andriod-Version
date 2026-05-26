package com.example.memeverseapp.utils;

import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.Locale;
import java.util.concurrent.TimeUnit;

public class TimeUtils {

    public static String getTimeAgo(String timestamp) {
        // Handle null or empty timestamp
        if (timestamp == null || timestamp.isEmpty()) {
            return "just now";
        }

        try {
            SimpleDateFormat sdf = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss", Locale.US);
            Date date = sdf.parse(timestamp);

            if (date == null) {
                return "just now";
            }

            long diff = System.currentTimeMillis() - date.getTime();

            long seconds = TimeUnit.MILLISECONDS.toSeconds(diff);
            if (seconds < 0) return "just now";
            if (seconds < 60) return "just now";

            long minutes = TimeUnit.MILLISECONDS.toMinutes(diff);
            if (minutes < 60) return minutes + " min ago";

            long hours = TimeUnit.MILLISECONDS.toHours(diff);
            if (hours < 24) return hours + " hours ago";

            long days = TimeUnit.MILLISECONDS.toDays(diff);
            if (days < 7) return days + " days ago";

            return new SimpleDateFormat("MMM d", Locale.US).format(date);
        } catch (ParseException e) {
            return "just now";
        } catch (Exception e) {
            return "just now";
        }
    }

    public static String formatJoinDate(String timestamp) {
        // Handle null or empty timestamp
        if (timestamp == null || timestamp.isEmpty()) {
            return "recently";
        }

        try {
            // Try different date formats
            SimpleDateFormat sdf = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss", Locale.US);
            Date date = sdf.parse(timestamp);

            if (date == null) {
                return "recently";
            }

            return new SimpleDateFormat("MMMM yyyy", Locale.US).format(date);
        } catch (ParseException e) {
            // Try without time
            try {
                SimpleDateFormat sdf2 = new SimpleDateFormat("yyyy-MM-dd", Locale.US);
                Date date = sdf2.parse(timestamp);
                if (date != null) {
                    return new SimpleDateFormat("MMMM yyyy", Locale.US).format(date);
                }
            } catch (ParseException e2) {
                // Ignore
            }
            return "recently";
        } catch (Exception e) {
            return "recently";
        }
    }
}
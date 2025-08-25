import {DateTime} from "luxon";

export const formatToDateTime = (date?: { date?: string, timezone?: string }): string => {
  if (!date || !date.date || !date.timezone) {
    return "";
  }

  return DateTime.fromSQL(date.date)
    .setZone(date.timezone)
    .toFormat('dd/MM/yyyy HH:mm:ss');
}
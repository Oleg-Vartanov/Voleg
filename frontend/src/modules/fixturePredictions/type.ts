import type { User } from '@/modules/user/type';

export interface Fixture {
  id: number | string;
  homeTeam: FixtureTeam;
  awayTeam: FixtureTeam;
  homeScore: number | null;
  awayScore: number | null;
  startAt: string;
}

export interface FixtureTeam {
  name: string;
}

export interface LeaderboardUser {
  user: User;
  periodPoints?: number | null;
  totalPoints?: number | null;
}

export interface PredictionUser {
  id: number | string;
}

export interface Prediction {
  user: PredictionUser;
  homeScore: number | null;
  awayScore: number | null;
  points: number | null;
}